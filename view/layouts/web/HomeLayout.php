<?php


class HomeLayout extends Layout
{
    public function setup()
    {

        $this->setTemplatePath('view/templates/web/')
        ->setTemplateFile('Home');
//
        $this->registerElement('Header', 'view/elements/web/common/');
        $this->registerElement('NavBar', 'view/elements/web/common/');
        $this->registerElement('NavBar', 'view/elements/web/common/');
        $this->registerElement('Home', 'view/elements/web/home/');

        $this->registerElement('Footer', 'view/elements/web/common/');


        $this->setPageTitle('Bản tin buổi sáng | Trang tin tức tổng hợp | Chào buổi sáng')
            ->setPageDescription('Tổng hợp tin tức mới nhất trong ngày, Bản tin buổi sảng cập nhật tin mới nhất về thời sự, giải trí, kinh tế, thời tiết và đời sống..')
            ->setPageKeywords('chào buổi sáng, tin buổi sáng, cà phê sáng, điểm tin sáng, tin giải trí buổi sáng, ban tin sáng, giải trí, ngôi sao, sao việt, tài chính, thời tiết, thể thao');

    }
}