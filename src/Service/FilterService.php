<?php

namespace App\Service;

use App\Model\ArticleManager;

class FilterService
{
    public function search($search){
        $articleManager = new ArticleManager();
        if (!empty($_POST['search'])) {
            return $articleManager->searchByModel($_POST['search']);
        }
        if (!empty($_POST['brand_id'])) {
            return $articleManager->searchByBrand($_POST['brand_id']);
        }
        if (!empty($_POST['color_id'])) {
            return $articleManager->searchByColor($_POST['color_id']);
        }
        if (!empty($_POST['size_id'])) {
            return $articleManager->searchBySize($_POST['size_id']);
        }
        if (!empty($_POST['brand_id']) && !empty($_POST['size_id']) && !empty($_POST['color_id'])) {
            return $articleManager->searchFull($_POST['color_id'], $_POST['size_id'], $_POST['brand_id']);
        }
    }
}