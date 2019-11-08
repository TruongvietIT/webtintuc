<?php


class Category extends Element
{
    public function setup()
    {
        $this->setTemplatePath('view/templates/web/common/')
            ->setTemplateFile('NavBar');
//        $cateModel = Context::getInstance()->getFront()->getModel("CategoryModel");
//        $this->assign('data', $cateModel);

    }
}