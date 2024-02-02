create table quantimodo_test.astral_chartjs_metric_values
(
    id             bigint unsigned auto_increment
        primary key,
    chartable_type varchar(255)                   not null,
    chartable_id   bigint unsigned                not null,
    metric_values  json                           null,
    chart_name     varchar(100) default 'default' not null,
    created_at     timestamp                      null,
    updated_at     timestamp                      null,
    constraint astral_chartjs_metric_values_chart_unique
        unique (chartable_type, chartable_id, chart_name)
)
    collate = utf8mb3_unicode_ci;

create index astral_chartjs_metric_values_chartable_type_chartable_id_index
    on quantimodo_test.astral_chartjs_metric_values (chartable_type, chartable_id);

