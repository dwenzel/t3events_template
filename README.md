# TYPO3 Events Template

> TYPO3 extension to create event records from templates

This package allows to create event records from templates. It is an extension to the TYPO3 CMS. 

## Requirements

* [TYPO3 CMS](https://github.com/TYPO3/TYPO3.CMS)
* [dwenzel/t3events](https://github.com/dwenzel/t3events)

## Installation

To install the TYPO3 Events Template extension in your project, you have to add the repository as a package within your `composer.json`:

```json
{
    "type": "vcs",
    "url": "git@github.com:dwenzel/t3events_template.git"
}
```

Install via ExtensionManager or composer:

```
composer require cpsit/t3events-template
```

## Configuration

### Template Enabled Types

In order to use templates a record type has to be enabled. 
Example:
```php
CPSIT\T3eventsTemplate\Utility\TableConfiguration::registerTemplateEnabledType(
  '1', 
  'event_type,headline,subtitle,teaser,description,content_elements,images,image,files,related,
               sys_language_uid,audience,organizer,genre,venue,keywords,performances,
               l10n_parent, l10n_diffsource,new_until,archive_date,hidden,starttime,endtime,fe_group,categories'
);
```
This enables the type '1' of table 'tx_t3events_domain_model_event' for template usage and determines which field should be copied when creating an new Event record from template.

Currently this works for table `tx_t3events_domain_model_event` only.


### Hide fields in new records

Fields can be hidden in new records by calling

```php
CPSIT\T3eventsTemplate\Utility\TableConfiguration::hideFieldsInNewRecords(
  'foo', 'bar'
 );
```

Note: This works only for fields where no display condition has be set or where the current display condition is a string.
