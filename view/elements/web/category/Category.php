<?php


class Category extends Element
{
    public function setup()
    {
        $this->setTemplatePath('view/templates/web/category/')
            ->setTemplateFile('Category');

        $uri = Context::getInstance()->getFront()->getRequest()->getFullUrl();
        if ($uri == rtrim($uri, '/') || $uri != strtolower($uri)) {
            Context::getInstance()->getFront()->getResponse()->setRedirect(rtrim(strtolower($uri), '/') . '/');
        }

        $newsModel = Context::getInstance()->getFront()->getModel('NewsModel');
        $slug = strtolower(Context::getInstance()->getRoute()->getParam('slug'));
        $pageIndex = Context::getInstance()->getRoute()->getParam('page');
        $pageIndex = $pageIndex > 1 ? $pageIndex : 1;

        $this->assign('pageIndex', $pageIndex);
        $data = $newsModel->getCategoryNews(array('category_slug' => $slug), $pageIndex);

        $this->assign('pageUrl', isset($data['category']['category_link']) ? $data['category']['category_link'] : '');
        $domain = Context::getInstance()->getFront()->getRequest()->getDomain() . '/';
        Context::getInstance()->addCachedParam('pageType', 2);
        Context::getInstance()->addCachedParam('canonicalLink', isset($data['category']['category_link']) ? $domain . $data['category']['category_link'] : $domain);
        if (isset($data['category']['category_name'])) {
            $title = isset($data['category']['category_title']) && !empty($data['category']['category_title']) ? $data['category']['category_title'] : $data['category']['category_name'];
            $description = isset($data['category']['category_description']) && !empty($data['category']['category_description']) ? $data['category']['category_description'] : $data['category']['category_name'];
            $keyword = isset($data['category']['category_keyword']) && !empty($data['category']['category_keyword']) ? $data['category']['category_keyword'] : $data['category']['category_name'];
            $this->assign('data', $data);
            Context::getInstance()->getFront()->getLayout()->setPageTitle($title)
                ->setPageDescription($description)
                ->setPageKeywords($keyword);
        } else {
            Context::getInstance()->getFront()->getResponse()->setRedirect('/notfound.html');
        }
    }
}