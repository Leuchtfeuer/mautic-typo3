.. include:: ../Includes.txt

.. _about:

=====
About
=====

This extension smoothly integrates `Mautic <https://www.acquia.com/products-services/mautic>`__ into your TYPO3
instance. It allows you to create your own Mautic forms in TYPO3 or integrating the existing forms in your website.

.. _about-compatibility:

Compatibility
=============

You need access to an Mautic instance. We are currently supporting following TYPO3 versions:

.. csv-table:: Version Matrix
   :header: "Extension Version", "TYPO3 v13 Support", "TYPO3 v12 Support", "TYPO3 v11 Support", "TYPO3 v10 Support", "TYPO3 v9 Support"
   :align: center

        "13.x", "Yes️", "No️", "No️", "No️", "No"
        "12.x", "No", "Yes️", "No️", "No️", "No"
        "4.4", "No️", "No️", "Yes", "No", "No️"
        "4.2 - 4.3", "No️", "No️", "Yes", "Yes️", "No️"
        "4.0 - 4.1", "No️", "No️", "No️", "Yes️", "No️"
        "3.x", "No️", "No️", "No️", "No️", "Yes"

.. csv-table:: Required Mautic Version
   :header: "Extension Version", "Minimum Mautic Version", "Recommended Mautic Version"
   :align: center

        "13.x", "4.4", "5.2 or later"
        "12.x", "4.4", "5.2 or later"
        "4.x", "2.16.0", ""
        "3.x", "2.14.2", ""

Starting with extension version 12.x all requests are authorized via OAuth2 (``/oauth/v2/authorize`` and
``/oauth/v2/token``), which requires the API to be enabled in your Mautic configuration. Mautic 4.4 is the lowest
version providing reliable OAuth2 refresh token handling together with all API endpoints used by this extension
(contacts, contact fields, companies, company fields, forms, segments, tags, assets and files). As Mautic 4.4 has
reached its end of life, we recommend running Mautic 5.2 or later.

.. _about-aboutMautic:

About Mautic
============

Mautic is a fully-featured marketing automation platform that enables organizations of all sizes to send multi-channel
communications at scale, and simultaneously personalize the experience for individual contacts. Mautic helps teams
gather important contact information, optimize and replicate campaigns, and ultimately, report on results. No other
solution compares to Mautic’s modern and flexible design which provides team members the freedom to move quickly and
adapt easily to changing business needs.

.. toctree::
    :maxdepth: 3
    :hidden:

    Contributing/Index
    Changelog/Index
