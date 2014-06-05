<?php
/**
 * @see modManagerResponse::loadControllerclass
 *
 * @var modManagerResponse $this
 * @var bool $prefixNamespace
 *
 * @var string $theme
 * @var array $paths
 * @var string $f
 * @var string $className
 * @var string $classFile
 * @var string $classPath
 */

if (!class_exists('ResourceHiderManagerController')) {
    require_once dirname(dirname(__DIR__)) . '/index.class.php';
}

class ResourceHiderHomeManagerController extends ResourceHiderManagerController
{
    public function loadCustomCssJs()
    {
        $this->addHtml(
<<<HTML
<script>
    Ext.onReady(function() {
        MODx.add('resourcehider-cmp');
    });
</script>
HTML
        );
    }
}

class ResourceHiderDefaultHomeManagerController extends ResourceHiderHomeManagerController
{

}
