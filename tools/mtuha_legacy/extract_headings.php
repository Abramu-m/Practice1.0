<?php
// Extract headings and table metadata from mtuha_months.php (legacy)
$path = __DIR__ . '/mtuha_months.php';
if (!file_exists($path)) {
    echo json_encode(['error' => 'file not found', 'path' => $path]) . "\n";
    exit(1);
}
$src = file_get_contents($path);

$result = [
    'main_heading' => null,
    'hospital_fields' => [],
    'age_bucket_headers' => [],
    'age_bucket_subheaders' => [],
    'section_headings' => [],
];

// Extract <thead> ... </thead>
if (preg_match('#<thead>(.*?)</thead>#is', $src, $m)) {
    $thead = $m[1];
    // main heading row: <td colspan="24"><strong>...</strong></td>
    if (preg_match('#<td[^>]*colspan="24"[^>]*>\s*<strong>(.*?)</strong>#is', $thead, $mm)) {
        $result['main_heading'] = trim(strip_tags($mm[1]));
    }

    // hospital fields row: look for substrings with PHP echo $row['...']
    if (preg_match_all("/#(\w+) la Kituo:.*?\\$row\['(.*?)'\]#is", $thead, $dummy)) {
        // fallback - not reliable, we'll search for $row['...'] occurrences
    }
    if (preg_match_all("/\\$row\['(.*?)'\]/", $thead, $hf)) {
        $result['hospital_fields'] = array_values(array_unique($hf[1]));
    }

    // Month/year fields (month is derived using date('F',...))
    if (strpos($thead, "date('F'") !== false) {
        $result['hospital_fields'][] = 'month_name';
    }
    if (strpos($thead, "echo $year") !== false || preg_match('/\\$year/', $thead)) {
        $result['hospital_fields'][] = 'year';
    }

    // Extract the age bucket header row(s) (two consecutive <tr style="background-color: grey;"> rows)
    if (preg_match_all('#<tr[^>]*style="[^"]*background-color:\s*grey;[^"]*"[^>]*>(.*?)</tr>#is', $src, $trs)) {
        // There are multiple grey trs; find the pair that contains the age bucket texts like "Umri chini ya mwezi 1"
        foreach ($trs[1] as $block) {
            if (stripos($block, 'Umri') !== false || stripos($block, 'JUMLA') !== false) {
                // Extract <td ...>...</td> cells in this block
                if (preg_match_all('#<td[^>]*>(.*?)</td>#is', $block, $cells)) {
                    $texts = array_map(function($s){ return trim(strip_tags($s)); }, $cells[1]);
                    // Append to age_bucket_headers if it looks like header row
                    $result['age_bucket_headers'][] = $texts;
                }
            }
        }
    }
}

// Extract section headings: tr with grey styling followed by Roman numeral in first td and title in second td
if (preg_match_all('#<tr[^>]*style="[^"]*background-color:\s*grey;[^"]*"[^>]*>\s*<td[^>]*>(.*?)</td>\s*<td[^>]*>(.*?)</td>\s*</tr>#is', $src, $sec)) {
    for ($i = 0; $i < count($sec[0]); $i++) {
        $first = trim(strip_tags($sec[1][$i]));
        $second = trim(strip_tags($sec[2][$i]));
        // Some grey rows are generic ("Diagnosis za OPD") where first cell may be &nbsp; or empty.
        // We consider rows where first cell contains Roman numerals or non-empty
        $first_clean = str_replace('&nbsp;', '', $first);
        if (preg_match('/^[IVXLCDM]+$/i', trim($first_clean))) {
            $result['section_headings'][] = ['index' => trim($first_clean), 'title' => $second];
        } else {
            // If first is empty but second looks like a major section title (e.g., Diagnosis za OPD), capture it
            if (trim($second) !== '') {
                // Avoid duplication: only add if it's not a smaller header like 'Diagnosis za OPD' immediately before numbered sections
                $result['section_headings'][] = ['index' => null, 'title' => $second];
            }
        }
    }
}

// Post-process: de-duplicate hospital fields
$result['hospital_fields'] = array_values(array_unique($result['hospital_fields']));

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
