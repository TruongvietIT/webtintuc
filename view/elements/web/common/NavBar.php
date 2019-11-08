<?php


class NavBar extends Element
{
    public function setup()
    {
        $this->setTemplatePath('view/templates/web/common/')
            ->setTemplateFile('NavBar');

        $cateModel = Context::getInstance()->getFront()->getModel("CategoryModel");
        $this->assign('categories', $cateModel);
    }
}