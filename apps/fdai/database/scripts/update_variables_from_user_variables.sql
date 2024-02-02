update variables join user_variables_aggregated on variables.id = user_variables_aggregated.variable_id
set variables.latest_tagged_measurement_time = user_variables_aggregated.latest_measurement_time,
    variables.earliest_tagged_measurement_time = user_variables_aggregated.earliest_measurement_time,
    variables.latest_measurement_time = user_variables_aggregated.latest_measurement_time,
    variables.earliest_measurement_time = user_variables_aggregated.earliest_measurement_time
