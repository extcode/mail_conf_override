.. include:: ../Includes.txt

============
Introduction
============

TYPO3 actually only allows to create an e-mail configuration.
There are different scenarios where you would like to use different senders.

* Multi domain installations with different domains for contact forms of the used languages.
* Different senders for different form types.
* Different senders for different functionalities (e.g. orders with extcode/cart).

The extension uses XCLASSes (Extending Classes) to modify a class of the TYPO3 core
to load the correct configuration based on the sender address of the email.
It was made sure to change as little as possible functionality of the TYPO3 core.
The change of the e-mail configuration was checked with functional tests.

.. toctree::
   :maxdepth: 5
   :titlesonly:

   Sponsoring/Index
   NoteOfThanks/Index
