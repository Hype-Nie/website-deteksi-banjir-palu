from pathlib import Path
import warnings
import joblib
import numpy as np
from sklearn.exceptions import InconsistentVersionWarning

ROOT = Path(__file__).resolve().parents[2]

class FloodModels:
    def __init__(self):
        # The supplied bundles record sklearn 1.9.1. Only their stored transformers
        # and centers are used; parity with saved training labels is checked in prepare.
        with warnings.catch_warnings():
            warnings.simplefilter('ignore', InconsistentVersionWarning)
            self.km = joblib.load(ROOT / 'model/model_kmeans_palu.pkl')
            self.fcm = joblib.load(ROOT / 'model/model_fcm_palu.pkl')

    @staticmethod
    def transform(bundle, rows):
        return bundle['minmax_scaler'].transform(bundle['quantile_transformer'].transform(rows)) * bundle['weights']

    def predict(self, rows):
        x = np.asarray(rows, dtype=float)
        km_x = self.transform(self.km, x)
        km_labels = np.argmin(np.linalg.norm(km_x[:, None] - self.km['model'].cluster_centers_, axis=2), axis=1)
        fcm_x = self.transform(self.fcm, x)
        distances = np.linalg.norm(fcm_x[:, None] - self.fcm['centers'], axis=2)
        # Closed-form FCM membership with fixed centers; deterministic and equivalent
        # to cmeans_predict. Exact-center samples divide membership over zero distances.
        zero = distances <= np.finfo(float).eps
        inverse = np.maximum(distances, np.finfo(float).eps) ** (-2 / (self.fcm['fuzzifier_m'] - 1))
        memberships = inverse / inverse.sum(axis=1, keepdims=True)
        for i in np.where(zero.any(axis=1))[0]:
            memberships[i] = zero[i] / zero[i].sum()
        fcm_labels = memberships.argmax(axis=1)
        output = []
        for i in range(len(x)):
            membership = {self.fcm['tier_map'][j]: round(float(v) * 100, 4) for j, v in enumerate(memberships[i])}
            certainty = float(memberships[i].max() * 100)
            output.append({'kmeans': self.km['tier_map'][int(km_labels[i])],
                           'fcm': self.fcm['tier_map'][int(fcm_labels[i])],
                           'membership': membership, 'certainty': round(certainty, 4),
                           'transition': certainty < 85})
        return output, km_x, km_labels, fcm_labels, memberships
