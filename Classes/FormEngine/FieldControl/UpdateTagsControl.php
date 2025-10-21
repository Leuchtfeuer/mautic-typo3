<?php

declare(strict_types=1);

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\FormEngine\FieldControl;

class UpdateTagsControl extends AbstractControl
{
    protected $tableName = 'tx_mautic_domain_model_tag';

    protected $action = 'updateTags';
}
