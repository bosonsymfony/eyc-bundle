<?php

namespace UCI\Boson\EyCBundle\Controller;

use UCI\Boson\BackendBundle\Controller\BackendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class AdminController extends BackendController
{
    /**
     * @Route(path="/eyc/admin/scripts/config.eyc.js", name="eyc_app_config")
     */
    public function getAppAction()
    {
        return $this->jsResponse('EyCBundle:Scripts:config.js.twig');
    }
    
   
    
}
