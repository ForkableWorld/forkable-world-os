create table quantimodo_test.media
(
    id                bigint unsigned auto_increment
        primary key,
    model_type        varchar(255)    not null,
    model_id          bigint unsigned not null,
    collection_name   varchar(255)    not null,
    name              varchar(255)    not null,
    file_name         varchar(255)    not null,
    mime_type         varchar(255)    null,
    disk              varchar(255)    not null,
    size              bigint unsigned not null,
    manipulations     json            not null,
    custom_properties json            not null,
    responsive_images json            not null,
    order_column      int unsigned    null,
    created_at        timestamp       null,
    updated_at        timestamp       null
)
    collate = utf8mb3_unicode_ci;

create index media_model_type_model_id_index
    on quantimodo_test.media (model_type, model_id);

