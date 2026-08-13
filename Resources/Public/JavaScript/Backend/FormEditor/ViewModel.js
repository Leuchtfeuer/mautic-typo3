/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 *
 ***/

import $ from 'jquery';
import * as StageComponent from '@typo3/form/backend/form-editor/stage-component.js';
import * as Helper from '@typo3/form/backend/form-editor/helper.js';

let _formEditorApp = null;

function getFormEditorApp() {
    return _formEditorApp;
}

function getPublisherSubscriber() {
    return getFormEditorApp().getPublisherSubscriber();
}

function getUtility() {
    return getFormEditorApp().getUtility();
}

function getHelper() {
    return Helper;
}

function getCurrentlySelectedFormElement() {
    return getFormEditorApp().getCurrentlySelectedFormElement();
}

function assert(test, message, messageCode) {
    return getFormEditorApp().assert(test, message, messageCode);
}

function _helperSetup() {
    assert('function' === $.type(Helper.bootstrap),
        'The view model helper does not implement the method "bootstrap"',
        1483708624
    );
    Helper.bootstrap(getFormEditorApp());
}

function _subscribeEvents() {
    getPublisherSubscriber().subscribe('view/inspector/editor/insert/perform', function (topic, args) {
        if (args[0]['templateName'] === 'Inspector-MauticPropertySelectEditor') {
            renderMauticPropertySelectEditor(
                args[0],
                args[1],
                args[2],
                args[3]
            );
        }
    });

    getPublisherSubscriber().subscribe('view/stage/abstract/render/template/perform', function (topic, args) {
        switch (args[0].get('type')) {
            case 'HiddenDate':
                StageComponent.renderSimpleTemplate(args[0], args[1]);
                break;
            case 'CountryList':
                getFormEditorApp().getViewModel().getStage().renderSimpleTemplateWithValidators(args[0], args[1]);
                break;
        }
    });
}

function renderMauticPropertySelectEditor(editorConfiguration, editorHtml, collectionElementIdentifier, collectionName) {
    let propertyData, propertyPath, selectElement;
    assert(
        'object' === $.type(editorConfiguration),
        'Invalid parameter "editorConfiguration"',
        1475421048
    );
    assert(
        'object' === $.type(editorHtml),
        'Invalid parameter "editorHtml"',
        1475421049
    );
    assert(
        getUtility().isNonEmptyString(editorConfiguration['label']),
        'Invalid configuration "label"',
        1475421050
    );
    assert(
        getUtility().isNonEmptyString(editorConfiguration['propertyPath']),
        'Invalid configuration "propertyPath"',
        1475421051
    );

    propertyPath = getFormEditorApp().buildPropertyPath(
        editorConfiguration['propertyPath'],
        collectionElementIdentifier,
        collectionName
    );

    getHelper()
        .getTemplatePropertyDomElement('label', editorHtml)
        .append(editorConfiguration['label']);

    selectElement = getHelper()
        .getTemplatePropertyDomElement('selectOptions', editorHtml);

    propertyData = getCurrentlySelectedFormElement().get(propertyPath);
    const options = $('option', selectElement);
    selectElement.empty();

    for (let i = 0, len = options.length; i < len; ++i) {
        let option;

        if (options[i]['value'] === propertyData) {
            option = new Option(options[i]['label'], i, false, true);
        } else {
            option = new Option(options[i]['label'], i);
        }
        $(option).data({value: options[i]['value'], type: options[i]['data-type']});
        selectElement.append(option);
    }

    selectElement.on('change', function () {
        getCurrentlySelectedFormElement().set(propertyPath, $('option:selected', $(this)).data('value'));
    });
}

export function bootstrap(formEditorApp) {
    _formEditorApp = formEditorApp;
    _helperSetup();
    _subscribeEvents();
}
