import json
import sys
from flood.inference import FloodModels
from flood.constants import LANDUSE


def main():
    action = sys.argv[1]
    if action == 'prepare':
        from flood.prepare import prepare
        result = prepare()
    elif action == 'predict':
        data = json.load(sys.stdin)
        features = [[data['rainfall'], data['elevation'], data['slope'], data['distance'], LANDUSE[data['landuse']]]]
        result = FloodModels().predict(features)[0][0]
    else:
        raise ValueError('Unsupported action')
    print(json.dumps(result, allow_nan=False))


if __name__ == '__main__':
    try:
        main()
    except Exception as exc:
        print(str(exc), file=sys.stderr)
        sys.exit(1)
