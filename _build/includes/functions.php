<?php
/**
 * Required functions to build the transport package
 *
 * @package resourcehider
 * @subpackage build
 */
function getSnippetContent($filename) {
    $o = file_get_contents($filename);
    $o = str_replace('<?php', '', $o);
    $o = str_replace('?>', '', $o);
    $o = trim($o);
    return $o;
}

function bld_policyFormatData($permissions) {
    $data = array();
    /** @var modAccessPolicy $permission */
    foreach ($permissions as $permission) {
        $data[$permission->get('name')] = true;
    }

    $data = json_encode($data);

    return $data;
}
