import sys
import unittest
import csv
import numpy as np
import skfuzzy as fuzz
from flood.inference import FloodModels, ROOT
from flood.constants import LANDUSE

class ModelParityTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.models = FloodModels()
        cls.rows = []
        with open(ROOT / 'model/dataset_banjir_palu_final3.csv') as stream:
            for row in csv.DictReader(stream):
                try:
                    values = [float(row[k]) for k in ['hujan_fix_1', 'elev_fix_1', 'slope_fix_1', 'distance']] + [LANDUSE[row['REMARK']]]
                    if np.isfinite(values).all(): cls.rows.append(values)
                except (ValueError, KeyError): pass

    def test_matches_saved_training_and_reference_fcm(self):
        output, weighted, labels, _, membership = self.models.predict(self.rows)
        np.testing.assert_array_equal(labels, self.models.km['model'].labels_)
        fcm = self.models.fcm
        reference, *_ = fuzz.cluster.cmeans_predict(
            self.models.transform(fcm, self.rows).T, fcm['centers'],
            m=fcm['fuzzifier_m'], error=1e-5, maxiter=150, seed=42)
        np.testing.assert_allclose(membership, reference.T, atol=1e-10, rtol=0)
        np.testing.assert_allclose(membership.sum(axis=1), 1)
        self.assertEqual(len(output), 1423)

    def test_extreme_inputs_remain_finite(self):
        result, *rest = self.models.predict([[0, -500, 0, 0, 1], [15000, 9000, 90, 1000000, 5]])
        for point in result:
            self.assertAlmostEqual(sum(point['membership'].values()), 100, places=3)
            self.assertTrue(0 <= point['certainty'] <= 100)

if __name__ == '__main__': unittest.main()
