<?php

namespace Hestia\WebApp\Installers\Resources;

use Hestia\System\HestiaApp;

class ComposerResource
{
    private $project;
    private $folder;
    private $appcontext;

    public function __construct(HestiaApp $appcontext, $data, $destination)
    {
        $this->folder = dirname($destination);
        $this->project = basename($destination);
        $this->appcontext = $appcontext;
        if (empty($data['version'])){
            $data['version'] = 2;
        }
        
        $this->appcontext->runComposer(["create-project", "--no-progress", "--prefer-dist", $data['src'], "-d " . $this->folder, $this->project ], $status, $data['version']);

        if($status->code !== 0){
            throw new \Exception("Error fetching Composer resource: " . $status->text);
        }
    }
}
