create table quantimodo_test.spreadsheet_importers
(
    id                            int unsigned auto_increment comment 'Spreadsheet Importer ID number'
        primary key,
    name                          varchar(30)                          not null comment 'Lowercase system name for the data source',
    display_name                  varchar(30)                          not null comment 'Pretty display name for the data source',
    image                         varchar(2083)                        not null comment 'URL to the image of the Spreadsheet Importer logo',
    get_it_url                    varchar(2083)                        null comment 'URL to a site where one can get this device or application',
    short_description             text                                 not null comment 'Short description of the service (such as the categories it tracks)',
    long_description              longtext                             not null comment 'Longer paragraph description of the data provider',
    enabled                       tinyint(1) default 1                 not null comment 'Set to 1 if the Spreadsheet Importer should be returned when listing Spreadsheet Importers',
    created_at                    timestamp  default CURRENT_TIMESTAMP not null,
    updated_at                    timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    client_id                     varchar(80)                          null,
    deleted_at                    timestamp                            null,
    wp_post_id                    bigint unsigned                      null,
    number_of_measurement_imports int unsigned                         null comment 'Number of Spreadsheet Import Requests for this Spreadsheet Importer.
                            [Formula:
                                update spreadsheet_importers
                                    left join (
                                        select count(id) as total, spreadsheet_importer_id
                                        from spreadsheet_importer_requests
                                        group by spreadsheet_importer_id
                                    )
                                    as grouped on spreadsheet_importers.id = grouped.spreadsheet_importer_id
                                set spreadsheet_importers.number_of_spreadsheet_importer_requests = count(grouped.total)
                            ]
                            ',
    number_of_measurements        int unsigned                         null comment 'Number of Measurements for this Spreadsheet Importer.
                                [Formula: update spreadsheet_importers
                                    left join (
                                        select count(id) as total, spreadsheet_importer_id
                                        from measurements
                                        group by spreadsheet_importer_id
                                    )
                                    as grouped on spreadsheet_importers.id = grouped.spreadsheet_importer_id
                                set spreadsheet_importers.number_of_measurements = count(grouped.total)]',
    sort_order                    int                                  not null,
    constraint spreadsheet_importers_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint spreadsheet_importers_wp_posts_ID_fk
        foreign key (wp_post_id) references quantimodo_test.wp_posts (ID)
)
    charset = utf8mb3;

