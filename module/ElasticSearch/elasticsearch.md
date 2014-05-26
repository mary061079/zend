###Structure of the Database

`CREATE TABLE `comments` (
 	`id` INT(11) NOT NULL AUTO_INCREMENT,
 	`author` INT(11) NOT NULL,
 	`comment` TEXT NULL,
 	`created` DATETIME NOT NULL,
 	`updated` DATETIME NOT NULL,
 	PRIMARY KEY (`id`),
 	INDEX `updated` (`updated`),
 	INDEX `created` (`created`)
 )
 COLLATE='latin1_swedish_ci'
 ENGINE=InnoDB;`

 `CREATE TABLE `cron` (
  	`id` INT(11) NOT NULL AUTO_INCREMENT,
  	`cron_name` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_bin',
  	`cron_value` VARCHAR(250) NOT NULL DEFAULT '0' COLLATE 'utf8_bin',
  	`date` DATETIME NOT NULL,
  	PRIMARY KEY (`id`),
  	UNIQUE KEY `cron_record` (`cron_name`,`cron_value`),
  	INDEX `cron_name` (`cron_name`),
  	INDEX `date` (`date`)
  )
  COLLATE='utf8_bin'
  ENGINE=InnoDB;`



###Install ElasticSearch
_1._ Download from here http://www.elasticsearch.org/overview/elasticsearch/ and unzip
_2._ Run  `/install/elasticsearch-0.19.0.RC1/bin/elasticsearch -f`
_3._ Check in the browser 'http://localhost:9200/` or `curl -X GET http://localhost:9200/`

###Set CronJob to add new sql data to the ES database ( once per day? )
`0 0 * * * php /www/zend/public/index.php esbulkactions`