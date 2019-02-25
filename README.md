# TechOlympics Website

TechOlympics is the nation's largest high school student-run tech conference.

## The Website

#### To see the website, go to [techolympics.org](http://techolympics.org).

## This Repository

This repository contains a selection of files relevant to the current 
TechOlympics website, which I created in 2017-18. Of course, sensitive 
information has been redacted/removed.

Some interesting files of note are in the root directory:

-   `report.sql`: This is a monster-length SQL query for generating a full 
    report of all users and their current registration status. I wrote it before
    I knew what an SQL `JOIN` was, and yet the query involves manually pseudo-
    join-ing across *several* tables using particularly convoluted manual checks
    on primary key values.

-   `custom-php.php`: Custom PHP code written by me that is injected into the 
    WordPress site.

-   `gravityforms-schema.json`: Exported file from GravityForms representing the
    structure of forms that make up the registration workflow.

-   `export-pages.xml`: WordPress export of all page content.

## License

Everything here is Copyright 2017-2019 (c) The INTERalliance of Greater 
Cincinnati. You do not have permission to share, modify, or publish this content
anywhere else, in any form, without written permission from me or the current 
Executive Director of the INTERalliance.