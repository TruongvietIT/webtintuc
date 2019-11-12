<?php


class Home extends Element
{
    public function setup()
    {
        $this->setTemplatePath('view/templates/web/home/')
            ->setTemplateFile('Home');

        $newsModel 	= Context::getInstance()->getFront()->getModel('NewsModel');
        $newsNew = $newsModel->getNewNews();
        $this->assign('newsNew', $newsNew);
    }
}