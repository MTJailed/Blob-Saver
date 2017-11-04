# Blob-Saver
Software for saving SHSH blobs written to support any PHP + curl enabled webserver

## About
SHSH2 Blobs are used by Apple in iOS firmware restores.

In the jailbreaking community they are saved to downgrade to the corresponding firmware of the blob.

Downgrading using these blobs requires a jailbreak or a specific workaround.

The SHSH2 saver in this repository has been build for webservers with PHP.

It can request the SHSH2 blobs from Apple's TSS server.

## Dependencies
- PHP
- PHP Curl (mostlikely already installed when using shared hosting)

## Disclaimer
This project does not require a VPS and neither uses exec functionality in PHP, therefore it will work with many shared hosting servers.
