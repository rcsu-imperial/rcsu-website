<?php

const BASE_ORG_CHART_HTML = <<<BASE_TEMPLATE
<div class="org-chart-container">
    <div class="top-level">
        <figure>
            <img src="PRESIDENT_IMAGE"
                 alt="PRESIDENT_NAME">
            <figcaption>PRESIDENT_NAME</figcaption>
            <p><strong>President</strong></p>
        </figure>
    </div>
    
    <ol class="level-wrapper level-2-wrapper">
        <li>
            <div class="level level-2">
                <figure>
                    <img src="SECRETARY_IMAGE"
                         alt="SECRETARY_NAME">
                    <figcaption>SECRETARY_NAME</figcaption>
                </figure>
                <p>Honorary Secretary</p>
            </div>
            <ol class="level-wrapper level-3-wrapper">
SUBCOMMITTEE_LIST
            </ol>
        </li>
    </ol>
</div>
BASE_TEMPLATE;

const SUBCOMMITTEE_TEMPLATE = <<<SUB_TEM
                <li>
                    <div class="level level-3">
                        <figure>
                            <img src="VP_IMAGE"
                                 alt="VP_NAME">
                            <figcaption>VP_NAME</figcaption>
                        </figure>
                        <p>VP_BRANCH</p>
                    </div>
                    <ol class="level-wrapper level-4-wrapper">
SUBCOMMITTEE_MEMBER_LIST
                    </ol>
                </li>
SUB_TEM;

const MEMBER_TEMPLATE = <<<MEM_TEM
                        <li>
                            <div class="level">
                                <figure>
                                    <img src="PERSON_IMAGE"
                                         alt="PERSON_NAME">
                                    <figcaption>PERSON_NAME</figcaption>
                                </figure>
                                <p>PERSON_ROLE</p>
                            </div>
                        </li>
MEM_TEM;

const DEFAULT_IMAGE_PATH = 'media/logo.png';
//const DEFAULT_IMAGE_PATH = 'https://via.placeholder.com/150.jpg';
const COMMITTEE_PROFILE_FOLDER_PATH = 'media/committee/';

$ROOT = dirname(__DIR__) ;
$file = $ROOT . '/xml/21-22.xml';

$people = simplexml_load_file($file);
if ($people === false) {
    throw new Exception('Could not parse provided committee file');
}
$year = $people['year'];

function getProfileImagePath(string $role): string {
    global $ROOT, $year;

    if (file_exists($ROOT . '/public/' . COMMITTEE_PROFILE_FOLDER_PATH . $year . '/' . $role . '.jpg')) {
        return COMMITTEE_PROFILE_FOLDER_PATH . $year . '/' . $role . '.jpg';
    } else if (file_exists($ROOT . '/public/' . COMMITTEE_PROFILE_FOLDER_PATH . $year . '/' . $role . '.jpeg')) {
        return COMMITTEE_PROFILE_FOLDER_PATH . $year . '/' . $role . '.jpeg';
    } else if (file_exists($ROOT . '/public/' . COMMITTEE_PROFILE_FOLDER_PATH . $year . '/' . $role . '.png')) {
        return COMMITTEE_PROFILE_FOLDER_PATH . $year . '/' . $role . '.png';
    } else {
        return DEFAULT_IMAGE_PATH;
    }
}

$subcommitteeListHtml = '';

foreach ($people as $person) {
    /* check that profile pic exists */
    $imgPath = getProfileImagePath($person['role']);

    /* SimpleXML is whack... you access the attributes of the XML element using the array offset syntax */
    switch ($person['role']) {
        case 'president':
            $orgChart = str_replace(['PRESIDENT_NAME', 'PRESIDENT_IMAGE'], [$person->name, $imgPath], BASE_ORG_CHART_HTML);
            break;
        case 'secretary':
            if (!isset($orgChart)) { throw new Exception('President should have been parsed before'); }
            $orgChart = str_replace(['SECRETARY_NAME', 'SECRETARY_IMAGE'], [$person->name, $imgPath], $orgChart);
            break;
        /* Those correspond to VPs */
        default:
            $displayRole = $person->{'display-role'};
            $memberListHtml = '';
            /* Parse subcommittee members */
            foreach ($person->subcommittee->person as $member) {
                $displayRoleMember = $member->{'display-role'};
                $imgPathMember = getProfileImagePath($member['role']);
                $memberListHtml .= str_replace(
                    ['PERSON_NAME', 'PERSON_IMAGE', 'PERSON_ROLE'],
                    [$member->name, $imgPathMember, $displayRoleMember],
                    MEMBER_TEMPLATE
                ) . "\n";
            }
            $subcommitteeListHtml .= str_replace(
                ['VP_NAME', 'VP_IMAGE', 'VP_BRANCH', 'SUBCOMMITTEE_MEMBER_LIST'],
                [$person->name, $imgPath, $displayRole, $memberListHtml],
                SUBCOMMITTEE_TEMPLATE
            ) . "\n";
    }
}

/* Add subcommittee list to org chart */
$orgChart = str_replace('SUBCOMMITTEE_LIST', $subcommitteeListHtml, $orgChart);
$websiteTemplate = file_get_contents(dirname(__FILE__) . '/template-website.html');
$website = str_replace('<!-- COMMITTEE_ORG_CHART -->', $orgChart, $websiteTemplate);

var_dump(file_put_contents($ROOT . '/public/index.html', $website));

