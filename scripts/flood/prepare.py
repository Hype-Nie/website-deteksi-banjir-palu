import csv
import hashlib
from datetime import datetime, timezone
import numpy as np
from pyproj import Transformer
from sklearn.metrics import davies_bouldin_score, silhouette_score
from .constants import LANDUSE, TIERS
from .inference import FloodModels, ROOT


def prepare():
    models = FloodModels()
    valid, features, excluded = [], [], 0
    with open(ROOT / 'model/dataset_banjir_palu_final3.csv', encoding='utf-8-sig', newline='') as source:
        for row in csv.DictReader(source):
            try:
                values = [float(row[k]) for k in ['hujan_fix_1', 'elev_fix_1', 'slope_fix_1', 'distance']]
                coords = [float(row[k]) for k in ['X', 'Y']]
                score = LANDUSE[row['REMARK']]
                if not all(np.isfinite(values + coords)):
                    raise ValueError('Non-finite data')
            except (ValueError, KeyError):
                excluded += 1
                continue
            valid.append((row, values, coords))
            features.append(values + [score])
    predictions, weighted, km_labels, fcm_labels, memberships = models.predict(features)
    saved = models.km['model'].labels_
    if len(saved) != len(km_labels) or not np.array_equal(saved, km_labels):
        raise ValueError('Dataset inference differs from saved K-Means training labels.')
    # UTM zone 50 south as explicitly documented in the supplied notebook.
    project = Transformer.from_crs('EPSG:32750', 'EPSG:4326', always_xy=True)
    points = []
    for (row, values, coords), prediction in zip(valid, predictions):
        lon, lat = project.transform(*coords)
        points.append({'id': int(row['id']), 'landuse': row['REMARK'],
                       'rainfall': values[0], 'elevation': values[1], 'slope': values[2], 'distance': values[3],
                       'x': coords[0], 'y': coords[1], 'lat': round(lat, 7), 'lng': round(lon, 7), **prediction})
    metrics = {}
    for name, labels in [('kmeans', km_labels), ('fcm', fcm_labels)]:
        metrics[name] = {'dbi': round(float(davies_bouldin_score(weighted, labels)), 6),
                         'silhouette': round(float(silhouette_score(weighted, labels)), 6),
                         'counts': {tier: sum(p[name] == tier for p in points) for tier in TIERS}}
    metrics['fpc'] = round(float(np.mean(np.sum(memberships ** 2, axis=1))), 6)
    metrics['agreement'] = round(sum(p['kmeans'] == p['fcm'] for p in points) / len(points) * 100, 4)
    metrics['transitions'] = sum(p['transition'] for p in points)
    return {'points': points, 'metrics': metrics, 'meta': {
        'total': len(points), 'excluded': excluded, 'raw_total': len(points) + excluded,
        'prepared_at': datetime.now(timezone.utc).isoformat(), 'crs': 'EPSG:32750',
        'parity': 'All predictions match the saved K-Means training labels',
        'landuses': sorted(LANDUSE.keys()),
        'source_hash': hashlib.sha256((ROOT / 'model/dataset_banjir_palu_final3.csv').read_bytes()).hexdigest()}}
