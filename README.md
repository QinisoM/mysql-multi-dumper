# mysql-multi-dumper
Web Interface for creating mysql multiple dump scripts.  Usefull for creating a smaller copy of a very large and busy database, e.g. Copying a production DB to a development

#System Requirements

PHP     : 5.4.* <br/>
MySql   : 5.6.10


To get started run the following command:
  php -S "IP:Port", e.g. 'php -S "127.0.0.1:12345"'

Navigate to the chosen location from your browser:
  http://IP:PORT"  , e.g. "http://127.0.0.1:12345"

You must complete all the required fields, once done, the following files will be create on the "Output" folder:
  - 'sql/'' (folder)
  - 'dump_commands.[ext]' (bat or sh)
  - 'restore_commands.[ext]' (bat or sh)

You can then run the created scripts, and the mysql dump and restore scripts will be placed in the sql

Contributors

