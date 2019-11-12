<?php


class CategoryLayout extends Layout
{
    public function setup()
    {
        $this->setTemplatePath('view/templates/web/')
            ->setTemplateFile('Category');

        $this->registerElement('Top', 'view/elements/web/common/');
        $this->registerElement('Header', 'view/elements/web/common/');

        $this->registerElement('Category', 'view/elements/web/category/');
        $this->registerElement('NavBar', 'view/elements/web/common/');

        $this->registerElement('Footer', 'view/elements/web/common/');

    }
}