<?php


class Header extends Element
{
    public function setup()
    {
        $this->setTemplatePath('view/templates/web/common/')
            ->setTemplateFile('Header');
    }
}