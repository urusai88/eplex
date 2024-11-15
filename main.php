<?php
function cmpNumber($a, $b): int
{
    if ($a > $b) {
        return 1;
    } else if ($a < $b) {
        return -1;
    }
    return 0;
}

/// ppp - price per pack
/// pc - packs count
function prepare($data, $n): array
{
    $data = array_filter($data, fn($v) => $v[1] > $v[3] && $v[3] < $n);
    $data = array_map(function ($v) {
        $v['ppp'] = $v[3] * $v[2];
        $v['pc'] = intval(floor($v[1] / $v[3]));
        return $v;
    }, $data);
    usort($data, fn($a, $b) => cmpNumber($a['ppp'], $b['ppp']));
    return $data;
}

function n($current, $cap)
{
    $result = $current;
    for ($i = count($current) - 1; $i >= 0; --$i) {
        if ($current[$i] > 0) {
            $result[$i]--;
            return $result;
        }
        $result[$i] = $cap[$i];
    }
    return null;
}

function array_to_string($array): string
{
    $parts = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $parts[] = array_to_string($value);
        } else {
            $parts[] = $value;
        }
    }
    return '[' . join(', ', $parts) . ']';
}

function compare_arrays(array $arr1, array $arr2): bool
{
    if (count($arr1) != count($arr2)) {
        return false;
    }
    var_dump($arr2);
    foreach ($arr1 as $k => $v) {
        var_dump(array_search($v, $arr2));
    }
    return false;
}

function f($data, $n): array
{
    $data = prepare($data, $n);
    $cap = array_map(fn($v) => $v['pc'], $data);

    $current = $cap;

    $found = [];

    do {
        $left = $n;
        foreach ($current as $i1 => $pc1) {
            $count = ($pc1 * $data[$i1][3]);
            $left -= $count;
            if ($left == 0) {
                $result = [];
                foreach ($current as $i2 => $pc2) {
                    if ($pc2 == 0) {
                        continue;
                    }
                    $result[] = [$data[$i2][0], $pc2 * $data[$i2][3]];
                }
                usort($result, fn($a, $b) => cmpNumber($a[0], $b[0]));
                $found[] = [
                    'result' => $result,
                    'sum' => computeCost($data, $result),
                ];
            } else if ($left < 0) {
                break;
            }
        }
    } while (($current = n($current, $cap)) != null);

    usort($found, fn($a, $b) => cmpNumber($a['sum'], $b['sum']));

    return $found[0]['result'];
}

function computeCost($data, $result): int
{
    $sum = 0;
    foreach ($result as $value) {
        $searchKey = null;
        foreach ($data as $dataKey => $dataValue) {
            if ($dataValue[0] == $value[0]) {
                $searchKey = $dataKey;
            }
        }
        $sum += ($data[$searchKey][2] * $value[1]);
    }
    return $sum;
}

$data1 = [
    [111, 42, 13, 1],
    [222, 77, 11, 10],
    [333, 103, 10, 50],
    [444, 65, 12, 5],
];

$data2 = [
    [111, 42, 9, 1],
    [222, 77, 11, 10],
    [333, 103, 10, 50],
    [444, 65, 12, 5],
];

$data3 = [
    [111, 100, 30, 1],
    [222, 60, 11, 10],
    [333, 100, 13, 50],
];

$result1 = [[111, 1], [222, 20], [333, 50], [444, 5]];
$result2 = [[111, 26], [333, 50]];
$result3 = [[111, 6], [222, 20], [333, 50]];

$data = [
    [$data1, 76, $result1],
    [$data2, 76, $result2],
    [$data2, 76, $result3],
];

foreach ($data as $i => $v) {
    $result = f($v[0], $v[1]);
    $expect = $v[2];
    $k = $i + 1;
    echo "---\n";
    echo "Result #$k: " . array_to_string($result) . '; sum: ' . computeCost($v[0], $result) . PHP_EOL;
    echo "Expect #$k: " . array_to_string($expect) . '; sum: ' . computeCost($v[0], $expect) . PHP_EOL;
}

