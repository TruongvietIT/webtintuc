<?php
class NotfoundLayout  extends Layout
{
    public function setup()
    {
        $notfound = Context::getInstance()->getFront()->getRequest()->getParts(0);
        if ($notfound !== 'notfound.html'){
            Context::getInstance()->getFront()->getResponse()->setRedirect('/notfound.html');
        }

        $this->setTemplatePath('view/templates/web/')
            ->setTemplateFile('Notfound');
        Context::getInstance()->getFront()->getResponse()->setHeader('HTTP/1.0 404 Not Found', 404);
        Context::getInstance()->getFront()->getResponse()->sendHeaders();

        $this->registerElement('Top', 'view/elements/web/common/');
        $this->registerElement('Header', 'view/elements/web/common/');

        $this->registerElement('Footer', 'view/elements/web/common/');
        $this->registerElement('Bottom', 'view/elements/web/common/');

        $this->setPageTitle('404 Notfound - Trang này không tồn tại!');
    }
}
