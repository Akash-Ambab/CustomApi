<?php
namespace Ambab\CustomApi\Api;

interface CustomOrderDetailsInterface {

    /**
    * @api
    * @param int $id 
    * @return object 
    */
    public function getOrderDetails($id);

}