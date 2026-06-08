<?php
/**
 * Simple converter for the WHO ICD-10 meta text files into a CSV for the Laravel seeder.
 *
 * Usage (from project root):
 *   php tools\icd102019enMeta\convert_meta_to_csv.php
 *
 * Output:
 *   database/seeders/data/icd10_who.csv
 */

$base = __DIR__;
$codesFile = $base . DIRECTORY_SEPARATOR . 'icd102019syst_codes.txt';
$groupsFile = $base . DIRECTORY_SEPARATOR . 'icd102019syst_groups.txt';
$chaptersFile = $base . DIRECTORY_SEPARATOR . 'icd102019syst_chapters.txt';

if (!file_exists($codesFile)) {
    fwrite(STDERR, "Missing codes file: $codesFile\n");
    exit(1);
}

// load groups: build both range list and id->label map
$groups = [];
$groupsById = [];
foreach (explode("\n", file_get_contents($groupsFile)) as $line) {
    $line = trim($line);
    if ($line === '') continue;
    $parts = explode(';', $line, 4);
    if (count($parts) < 4) continue;
    list($start, $end, $grpId, $label) = $parts;
    $start = trim($start);
    $end = trim($end);
    $grpId = trim($grpId);
    $label = trim($label);
    $groups[] = [
        'start' => $start,
        'end' => $end,
        'label' => $label,
        'id' => $grpId,
    ];
    if ($grpId !== '') {
        $groupsById[$grpId] = $label;
    }
}

// load chapters mapping (id -> name)
$chapters = [];
foreach (explode("\n", file_get_contents($chaptersFile)) as $line) {
    $line = trim($line);
    if ($line === '') continue;
    $parts = explode(';', $line, 2);
    if (count($parts) < 2) continue;
    $chapters[trim($parts[0])] = trim($parts[1]);
}

// helper to find group label for a code like 'A00' or 'A00.1' -> compare prefix with ranges
function findGroupLabelByPrefix($code, $groups)
{
    $m = strtoupper($code);
    $norm = preg_replace('/[^A-Z0-9]/', '', $m);
    $prefix = substr($norm, 0, 3);
    foreach ($groups as $g) {
        if ($prefix >= $g['start'] && $prefix <= $g['end']) {
            return $g['label'];
        }
    }
    return null;
}

$outDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR . 'data';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$outPath = $outDir . DIRECTORY_SEPARATOR . 'icd10_who.csv';
// open output CSV
$fp = fopen($outPath, 'w');
fputcsv($fp, ['code','description','category','subcategory','chapter','notes']);

// open codes file
$in = fopen($codesFile, 'r');
if (!$in) {
    fwrite(STDERR, "Failed to open codes file\n");
    exit(1);
}

$count = 0;
while (($line = fgets($in)) !== false) {
    $line = trim($line);
    if ($line === '') continue;
    $parts = explode(';', $line);

    // Preferred fixed-index extraction (based on observed file structure):
    // index 5: specific code (e.g., A00.0)
    // index 6: another code variant
    // index 7: numeric mapping (ignore)
    // index 8: description
    // index 9: short category/subcategory label
    // index 10-11: sometimes notes or empty
    // index 12: group id (numeric) -> maps to groupsById

    // extract code: prefer index 5 then 6 then fallback search
    $code = null;
    if (isset($parts[5]) && preg_match('/^[A-Z][0-9]{2}(\.[0-9A-Z]+)?$/i', trim($parts[5]))) {
        $code = strtoupper(trim($parts[5]));
    } elseif (isset($parts[6]) && preg_match('/^[A-Z][0-9]{2}(\.[0-9A-Z]+)?$/i', trim($parts[6]))) {
        $code = strtoupper(trim($parts[6]));
    } else {
        // fallback: scan columns for a code-like token
        foreach ($parts as $p) {
            $ptrim = trim($p);
            if (preg_match('/^[A-Z][0-9]{2}(\.[0-9A-Z]+)?$/i', $ptrim)) {
                $code = strtoupper($ptrim);
                break;
            }
        }
    }

    // description: prefer index 8, then 9, then fallback to first textual col
    $description = null;
    if (isset($parts[8]) && trim($parts[8]) !== '') {
        $description = trim($parts[8]);
    } elseif (isset($parts[9]) && trim($parts[9]) !== '') {
        $description = trim($parts[9]);
    } else {
        foreach ($parts as $p) {
            $ptrim = trim($p);
            if ($ptrim === '') continue;
            if (strlen($ptrim) > 3 && preg_match('/[a-zA-Z]/', $ptrim) && !preg_match('/^[A-Z][0-9]{2}/', $ptrim)) {
                $description = $ptrim;
                break;
            }
        }
    }

    if (!$code || !$description) {
        // skip entries we can't parse
        continue;
    }

    // category: prefer explicit group id at index 12, else try range mapping
    $category = null;
    if (isset($parts[12]) && preg_match('/^\d+$/', trim($parts[12]))) {
        $gid = ltrim(trim($parts[12]), '0');
        // groupsById keys may be zero-padded in the file; try raw match then padded
        if (isset($groupsById[trim($parts[12])])) {
            $category = $groupsById[trim($parts[12])];
        } elseif (isset($groupsById[$gid])) {
            $category = $groupsById[$gid];
        }
    }
    if ($category === null) {
        $category = findGroupLabelByPrefix($code, $groups);
    }

    // chapter: use prefix to find group's chapter id (groups contain id mapping to chapters file in groupsById)
    $chapter = null;
    if (isset($parts[12]) && trim($parts[12]) !== '') {
        $gid = ltrim(trim($parts[12]), '0');
        // try both padded and unpadded
        $gidKey = trim($parts[12]);
        if (isset($groupsById[$gidKey])) {
            // groups file doesn't include chapter number directly, but many labels include numbering; try to find chapter via groups entry
            // fallback: leave chapter empty here and map by prefix to chapters map
        }
    }
    // Map chapter by code prefix using the groups ranges -> try to find matching group then lookup chapter by searching groups file for a numeric group id
    // We'll derive chapter from the lexicographic ranges by mapping the prefix to the chapters mapping: extract chapter number from groups array indices
    foreach ($groups as $g) {
        $prefix = substr(preg_replace('/[^A-Z0-9]/', '', $code), 0, 3);
        if ($prefix >= $g['start'] && $prefix <= $g['end']) {
            // prefer to use the group's id to lookup chapter name from the chapters map
            $gid = $g['id'];
            if ($gid !== '' && isset($chapters[$gid])) {
                $chapter = $chapters[$gid];
            }
            break;
        }
    }

    // subcategory: prefer index 9 (often a short grouping label) if it's text
    $subcategory = null;
    if (isset($parts[9]) && trim($parts[9]) !== '' && !preg_match('/^[A-Z][0-9]/', trim($parts[9]))) {
        $subcategory = trim($parts[9]);
    }

    // notes: collect any extra textual columns that look like human notes (exclude numeric ids and codes)
    $notesParts = [];
    foreach ($parts as $idx => $col) {
        $col = trim($col);
        if ($col === '') continue;
        // skip columns we've already used
        if (in_array($idx, [5,6,7,8,9,12])) continue;
        // skip short numeric or hyphenated codes like '4-002' or pure numbers
        if (preg_match('/^[0-9\-]+$/', $col)) continue;
        // skip columns that look like machine codes (A000 style)
        if (preg_match('/^[A-Z][0-9]{3,}$/i', $col)) continue;
        // accept longer textual columns
        if (strlen($col) > 3 && preg_match('/[a-zA-Z]/', $col)) {
            $notesParts[] = $col;
        }
    }
    $notes = count($notesParts) ? implode(' | ', $notesParts) : '';

    fputcsv($fp, [$code, $description, $category, $subcategory, $chapter, $notes]);
    $count++;
}

fclose($in);
fclose($fp);

echo "Wrote $count rows to $outPath\n";

exit(0);
