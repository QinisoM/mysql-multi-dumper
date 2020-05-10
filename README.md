# MySQL Multiple Dumper

[![SymfonyInsight](https://insight.symfony.com/projects/46d81ece-eab4-46aa-b805-bb76f93bf565/mini.svg)](https://insight.symfony.com/projects/46d81ece-eab4-46aa-b805-bb76f93bf565)

Web Interface for creating MySQL multiple dump scripts.  Useful for creating a smaller copy of a very large and busy database, e.g. Copying a production DB to a development environment.

# Features
- Ability to select the database(s) you need
- Ability to select the table(s) you need
- Ability to specify how many rows to include
- Ability to specify how many rows to include per dump file (useful for creating smaller dump files)
- Ability to choose to generate either Windows Batch or linux Bash scripts
- Option to add lock options to generates scripts
- Option to compress(gzip) dump files to save space

# System Requirements
PHP     : 7.* <br/>

To get started run the following command:
  php -S "IP:Port", e.g. 'php -S "127.0.0.1:12345"'

Navigate to the chosen location from your browser:
  http://IP:PORT"  , e.g. "http://127.0.0.1:12345"

You must complete all the required fields, once done, the following files will be created on the "Output" folder:
  - 'sql/'' (folder)
  - 'dump_commands.[ext]' (bat or sh)
  - 'restore_commands.[ext]' (bat or sh)

You can then run the created scripts, and the MySQL dump and restore scripts will be placed in the sql folder.

# Find this project useful ? :heart:
* Support it by clicking the :star: button on the upper right of this page. :v:

# Contributors

<ul class="task-list">
  <li>
    <a href="https://github.com/QinisoM">Qiniso M</a>
  </li>
  <li>
    <a href="https://github.com/kgundula">KG</a>
  </li>
</ul>
