<?php

class Top extends Element
{
    public function setup()
    {
        $this->setTemplatePath('view/templates/web/common/')
            ->setTemplateFile('Top');
    }
}