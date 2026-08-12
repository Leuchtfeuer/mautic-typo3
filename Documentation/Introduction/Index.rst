.. include:: ../Includes.txt

.. _introduction:

============
Introduction
============

This chapter gives a basic introduction about the TYPO3 CMS extension "*mautic*".

Target Audience
===============

This extension is intended for those who use the marketing automation tool Mautic. This manual will walk you through the steps of
connecting your TYPO3 installation to your Mautic installation, as well as how to utilize the features of the extension.

Features
========

- Dynamic content blocks based on user behaviour
- Form synchronization between TYPO3 and Mautic
- Mautic form actions in TYPO3
- Mautic form embedding
- Tracking script integration
- OAuth2 support
- Tag users
- Link assets (read-only)

Requirements
============

The Mautic extension requires the TYPO3 Extension
`marketing_automation <https://github.com/Leuchtfeuer/typo3-marketing-automation>`_. For API calls it also requires the
`Mautic API Library <https://github.com/mautic/api-library>`_.

Suggestions
===========

If you want to use your own forms provided by TYPO3, you need to install the TYPO3 Extension
`form <https://github.com/TYPO3/typo3/tree/main/typo3/sysext/form>`_.

See also
========

- https://github.com/mautic/mautic-typo3
- https://extensions.typo3.org/extension/mautic/

Compatibility
=============

This extension works with TYPO3 v13 LTS and requires PHP 8.2 or later. It connects to a Mautic instance in version 4.4
or later. See :ref:`about-compatibility` for the full version matrix.
