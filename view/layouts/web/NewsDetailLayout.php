<?php


class NewsDetailLayout extends Layout
{
    public function setup()
    {


        $this->setTemplatePath('view/templates/web/')
            ->setTemplateFile('NewsDetail');
//

        $this->registerElement('Top', 'view/elements/web/common/');

        $this->registerElement('Header', 'view/elements/web/common/');

        $this->registerElement('NewsDetail', 'view/elements/web/news/');

        $this->registerElement('NavBar', 'view/elements/web/common/');

        $this->registerElement('Footer', 'view/elements/web/common/');

//        var_dump(22222);
    }
}