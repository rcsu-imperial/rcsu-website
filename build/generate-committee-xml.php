<?php

/***
 * This only generates a draft from which to work on as Dep Soc president, Academic and Wellbeing reps are not available
 * from the union eActivity API
 * Departmental soc Presidents go under the Treasurer
 * Academic reps go under The VP of Education
 * Wellbeing reps go under the VP of Wellbeing
 ***/

const EACTIVITY_API_KEY = '7D93ED30-B6CF-47C3-A121-68355B2E0CAF';
/** CSP id number */
const RCSU_EACTIVITY_ID = '730';
const EACTIVITY_BASE_API_HOST = 'https://eactivities.union.ic.ac.uk/API';

function eActivityApiCallV1($endpoint, $method = 'GET', $data = [])
{
    if (EACTIVITY_API_KEY === '') {
        throw new Exception("Must provide an eActivity API key");
    }
    
    $headers = ['X-API-Key: ' . EACTIVITY_API_KEY];
    $uri = EACTIVITY_BASE_API_HOST . $endpoint;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $uri);
    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);
    $results = json_decode($response, true);
    
    if (!curl_errno($curl)) {
        switch ($http_code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE)) {
            case 200:
                break;
            case 401:
                throw new Exception('Invalid API key');
            case 403:
                throw new Exception('IP BAN');
            default:
                throw new Exception('Unexpected HTTP code: ' . $http_code);
        }
    }
    curl_close($curl);
    return $results;
}

function personXML(string $role, array $data, int $baseIndent): string
{
    return "\n" . str_repeat(' ', $baseIndent) . '<person role="'. $role .'">' . "\n"
        . str_repeat(' ', $baseIndent+1) . '<display-role>'.$data['display'].'</display-role>'
        . "\n" . str_repeat(' ', $baseIndent+1) . '<name>'.$data['name'].'</name>'
        . "\n" . str_repeat(' ', $baseIndent+1) . '</person>';
}


/**
$structure = [
    'president',
    'secretary',
    'vice-president-activities' => [
        'events-officer',
        'publicity-officier',
        'rag-officer',
        'sports-officer',
    ],
    'vice-president-operation' => [
        'deputy-broadsheet-editor',
        'broadsheet-editor',
        'webmaster',
        'sponsorship-officer',
        'science-challenge-chair',
        'alumni-officer-theta-bearer',
    ],
    'treasurer' => [
        
    ],
    'vice-president-welfare-wellbeing' => [],
    'vice-president-education' => [],
];
 **/

const EXECUTIVE_ROLES = [
    'president',
    'secretary',
    'treasurer',
    'vice-president-activities',
    'vice-president-education',
    'vice-president-operations',
    'vice-president-welfare',
];

const ACTIVITIES_ROLES = [
    'events-officer',
    'publicity-officer',
    'rag-officer',
    'sports-officer',
];

const OPERATIONS_ROLES = [
    'deputy-broadsheet-editor',
    'broadsheet-editor',
    'webmaster',
    'sponsorship-officer',
    'science-challenge-chair',
    'alumni-officer-theta-bearer',
];

const WELLBEING_ROLES = [
    'wellbeing-activities-officer',
];
const EDUCATION_ROLES = [];
const TREASURER_ROLES = [];

$year = date("y") . '-' . (date("y") + 1);
$exec = [];
$activities = [];
$operations = [];
$education = [];
$welfare = [];
$treasurer = [];

$response = eActivityApiCallV1('/CSP/' . RCSU_EACTIVITY_ID . '/reports/committee');
foreach ($response as $committeeMember) {
    $roleDisplay = trim($committeeMember['PostName']);
    /* Handle '&' */
    $roleDisplay = str_replace('&', '&amp;', $roleDisplay);
    $role = str_replace(' ', '-', strtolower($roleDisplay));
    /* Handle edge cases for welfare role */
    $role = str_replace('-&amp;-wellbeing', '', $role);
    /* Handle edge case for vice pres roles */
    $role = str_replace(['(', ')'], '', $role);
    $name = $committeeMember['FirstName'] . ' ' . $committeeMember['Surname'];
    
    if (in_array($role, EXECUTIVE_ROLES)) {
        $exec[$role] = ['display' => $roleDisplay, 'name' => $name];
        continue;
    }
    if (in_array($role, ACTIVITIES_ROLES)) {
        $activities[$role] = ['display' => $roleDisplay, 'name' => $name];
        continue;
    }
    if (in_array($role, OPERATIONS_ROLES)) {
        $operations[$role] = ['display' => $roleDisplay, 'name' => $name];
        continue;
    }
    if (in_array($role, WELLBEING_ROLES)) {
        $welfare[$role] = ['display' => $roleDisplay, 'name' => $name];
        continue;
    }
    $treasurer[$role] = ['display' => $roleDisplay, 'name' => $name];
}

/* Manually add templates for President, Academic and Wellbeing reps */
$treasurer = $treasurer + [
        'biosoc-president' => ['display' => 'BioSoc President', 'name' => 'BioSocPresident Name'],
        'biochemsoc-president' => ['display' => 'BiochemSoc President', 'name' => 'BiochemSocPresident Name'],
        'chemsoc-president' => ['display' => 'ChemSoc President', 'name' => 'ChemSocPresident Name'],
        'mathsoc-president' => ['display' => 'MathSoc President', 'name' => 'MathSocPresident Name'],
        'physoc-president' => ['display' => 'PhySoc President', 'name' => 'PhySocPresident Name'],
    ];

$education = $education + [
        'bio-academic' => ['display' => 'Biology academic representative', 'name' => 'NAME TO FILL IN'],
        'biochem-academic' => ['display' => 'Biochemistry academic representative', 'name' => 'NAME TO FILL IN'],
        'chem-academic' => ['display' => 'Chemistry academic representative', 'name' => 'NAME TO FILL IN'],
        'maths-academic' => ['display' => 'Mathematics academic representative', 'name' => 'NAME TO FILL IN'],
        'physic-academic' => ['display' => 'Physics academic representative', 'name' => 'NAME TO FILL IN'],
    ];
$welfare = $welfare + [
        'bio-wellbeing' => ['display' => 'Biology wellbeing representative', 'name' => 'NAME TO FILL IN'],
        'biochem-wellbeing' => ['display' => 'Biochemistry wellbeing representative', 'name' => 'NAME TO FILL IN'],
        'chem-wellbeing' => ['display' => 'Chemistry wellbeing representative', 'name' => 'NAME TO FILL IN'],
        'maths-wellbeing' => ['display' => 'Mathematics wellbeing representative', 'name' => 'NAME TO FILL IN'],
        'physic-wellbeing' => ['display' => 'Physics wellbeing representative', 'name' => 'NAME TO FILL IN'],
    ];

$xml = '<?xml version="1.0" encoding="utf-8" standalone="no" ?>
<!-- <!DOCTYPE committee SYSTEM "./committee.dtd"> -->
<committee year="'. $year .'">';

foreach ($exec as $role => $info) {
    if (strpos($role, 'vice-president') !== false) {
        $xml .= '
 <person role="'. $role .'">
  <display-role>'.$info['display'].'</display-role>
  <name>'.$info['name'].'</name>
  <subcommittee>';
        
        $name = substr($role, strlen('vice-president-'));
        foreach ($$name as $subRole => $data) {
            $xml .= personXML($subRole, $data, 3);
        }
        
        $xml .= '
  </subcommittee>
 </person>';
        continue;
    }
    if ($role === 'treasurer') {
        $xml .= '
 <person role="'. $role .'">
  <display-role>'.$info['display'].'</display-role>
  <name>'.$info['name'].'</name>
  <subcommittee>';
        
        foreach ($$role as $subRole => $data) {
            $xml .= personXML($subRole, $data, 3);
        }
        
        $xml .= '
  </subcommittee>
 </person>';
        continue;
    }
    
    $xml .= personXML($role, $info, 1);
}

$xml .= "\n</committee>\n";

file_put_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . $year . '.xml', $xml);
