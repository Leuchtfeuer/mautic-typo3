<?php

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\FormEngine\FieldControl;

use TYPO3\CMS\Backend\Form\AbstractNode;

abstract class AbstractControl extends AbstractNode
{
    protected $tableName;

    protected $action;

    #[\Override]
    public function render()
    {
        return [
            'iconIdentifier' => 'actions-refresh',
            'title' => 'updateTagsControl',
            'linkAttributes' => [
                'onClick' => $this->getOnClickJS(),
                'href' => '#',
            ],
        ];
    }

    protected function getOnClickJS(): string
    {
        return <<<JS
require(['TYPO3/CMS/Backend/FormEngine', 'TYPO3/CMS/Backend/Modal'], function (FormEngine, Modal) {
    Modal.confirm(
        TYPO3.lang["FormEngine.refreshRequiredTitle"],
        TYPO3.lang["FormEngine.refreshRequiredContent"]
    ).on("button.clicked", function(event) {
        if (event.target.name === "ok") {
            let input = document.createElement("input");
            input.type = "hidden";
            input.name = "{$this->tableName}[{$this->action}]";
            input.value = '1';
            document.getElementsByName(FormEngine.formName).item(0).appendChild(input);
            FormEngine.saveDocument();
        }
        Modal.dismiss();
    });
return false;});
JS;
    }
}
