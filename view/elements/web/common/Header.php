<?php


class Header extends Element
{
    public function setup()
    {
        $this->setTemplatePath('view/templates/web/common/')
            ->setTemplateFile('Header');

        $newsModel = Context::getInstance()->getFront()->getModel("NewsModel");
        $categories = $newsModel->getHeaderCategory();
        $this->assign('categories', $categories);

    }
}