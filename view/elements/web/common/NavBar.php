<?php


class NavBar extends Element
{
    public function setup()
    {
        $this->setTemplatePath('view/templates/web/common/')
            ->setTemplateFile('NavBar');
//
        $newsModel = Context::getInstance()->getFront()->getModel('NewsModel');
//
        $mostRead = $newsModel->getNews(array('status' => 1, 'order_by' => 'visit_count'), 1, 10);
        $this->assign('data', $mostRead);

    }
}