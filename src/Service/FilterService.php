<?php

namespace App\Service;

use App\Model\ArticleManager;

class FilterService
{
    public function getArticlesFromSearch(array $search){
        $articleManager = new ArticleManager();
        if (!empty($search['search'])) {
            return $articleManager->searchByModel($search['search']);
        }
        if (!empty($search['brand_id'])) {
            return $articleManager->searchByBrand($search['brand_id']);
        }
        if (!empty($search['color_id'])) {
            return $articleManager->searchByColor($search['color_id']);
        }
        if (!empty($search['size_id'])) {
            return $articleManager->searchBySize($search['size_id']);
        }
        if (!empty($search['brand_id']) && !empty($search['size_id']) && !empty($search['color_id'])) {
            return $articleManager->searchFull($search['color_id'], $search['size_id'], $search['brand_id']);
        }
    }
}