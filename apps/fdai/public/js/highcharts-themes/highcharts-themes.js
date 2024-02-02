var dataLabelFontSize = '20px';
var highstockNavigatorFontSize = '12px';
var largeButtonFontSize = '12px';
var subtitleFontSize = '12px'; // Keep small so we don't squish charts vertically
var titleFontSize = '18px';
var tooltipFontSize = '16px';
var xAxisLabelFontSize = '10px';
var xAxisTitleFontSize = '10px';
var yAxisFontSize = '10px'; // Keep small or it squishes mobile

    Highcharts.themes = {};

  colorSchemes = {
    base: ['#dd7722', '#2288cc', '#dd3322', '#22aa99', '#bb4488', '#ddaa00', '#6655cc', '#99aa00'],
    pastel: ['#E6645C', '#55A9DC', '#886DB3', '#6CC080'],
    steel: ['#484D59', '#aaaaaa', '#4295F3'],
    future: ['#E6645C', '#55A9DC', '#886DB3', '#6CC080']
  };

  markers = {
    base: {
      enabled: true,
      lineWidth: 1,
      radius: 2,
      fillColor: '#FFFFFF',
      lineColor: null,
      symbol: 'circle',
      states: {
        hover: {
          enabled: false,
          radius: 1,
          lineWidth: 5
        }
      }
    },
    pastel: {
      enabled: true,
      lineWidth: 3,
      radius: 5,
      fillColor: null,
      lineColor: '#FFFFFF',
      symbol: 'circle',
      states: {
        hover: {
          lineWidth: 5,
          radius: 7
        }
      }
    },
    steel: {
      enabled: true,
      lineWidth: 2,
      radius: 5,
      fillColor: '#FFFFFF',
      lineColor: null,
      symbol: 'circle',
      states: {
        hover: {
          lineWidth: 3,
          radius: 6
        }
      }
    },
    future: {
      enabled: true,
      lineWidth: 8,
      radius: 5,
      fillColor: null,
      lineColor: 'rgba(0, 0, 0, 0.15)',
      symbol: 'circle',
      states: {
        hover: {
          lineWidth: 0,
          radius: 10
        }
      }
    }
  };

    Highcharts.themes.pastel = {
    colors: colorSchemes.pastel,
    plotOptions: {
      line: {
        lineWidth: 3,
        marker: markers.pastel
      },
      bar: {
        pointWidth: 1
      },
      column: {
        pointWidth: 1
      }
    }
  };

    Highcharts.themes.steel = {
    colors: colorSchemes.steel,
    plotOptions: {
      line: {
        marker: markers.steel
      },
      bar: {
        pointWidth: 1
      },
      column: {
        pointWidth: 1
      }
    }
  };

    Highcharts.themes.future = {
    colors: colorSchemes.future,
    plotOptions: {
      line: {
        marker: markers.future
      },
      bar: {
        pointWidth: 1
      },
      column: {
        pointWidth: 1
      }
    }
  };

    Highcharts.themes.dark = {
      colors: ['#2b908f', '#90ee7e', '#f45b5b', '#7798BF', '#aaeeee', '#ff0066',
          '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
      chart: {
          backgroundColor: {
              linearGradient: { x1: 0, y1: 0, x2: 1, y2: 1 },
              stops: [
                  [0, '#2a2a2b'],
                  [1, '#3e3e40']
              ]
          },
          style: {
              fontFamily: '\'Unica One\', sans-serif'
          },
          plotBorderColor: '#606063'
      },
      title: {
          style: {
              color: '#E0E0E3',
              //textTransform: 'uppercase',
              fontSize: titleFontSize
          }
      },
      subtitle: {
          style: {
              color: '#E0E0E3',
              //textTransform: 'uppercase'
          }
      },
      xAxis: {
          gridLineColor: '#707073',
          labels: {
              style: {
                  color: '#E0E0E3'
              }
          },
          lineColor: '#707073',
          minorGridLineColor: '#505053',
          tickColor: '#707073',
          title: {
              style: {
                  color: '#A0A0A3'
              }
          }
      },
      yAxis: {
          gridLineColor: '#707073',
          labels: {
              style: {
                  color: '#E0E0E3'
              }
          },
          lineColor: '#707073',
          minorGridLineColor: '#505053',
          tickColor: '#707073',
          tickWidth: 1,
          title: {
              style: {
                  color: '#A0A0A3'
              }
          }
      },
      tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.85)',
          style: {
              color: '#F0F0F0'
          }
      },
      plotOptions: {
          series: {
              dataLabels: {
                  color: '#F0F0F3',
                  style: {
                      fontSize: dataLabelFontSize
                  }
              },
              marker: {
                  lineColor: '#333'
              }
          },
          boxplot: {
              fillColor: '#505053'
          },
          candlestick: {
              lineColor: 'white'
          },
          errorbar: {
              color: 'white'
          }
      },
      legend: {
          //backgroundColor: 'rgba(0, 0, 0, 0.5)',
          itemStyle: {
              color: '#E0E0E3'
          },
          itemHoverStyle: {
              color: '#FFF'
          },
          itemHiddenStyle: {
              color: '#606063'
          },
          title: {
              style: {
                  color: '#C0C0C0'
              }
          }
      },
      credits: {
          style: {
              color: '#666'
          }
      },
      labels: {
          style: {
              color: '#707073'
          }
      },
      drilldown: {
          activeAxisLabelStyle: {
              color: '#F0F0F3'
          },
          activeDataLabelStyle: {
              color: '#F0F0F3'
          }
      },
      navigation: {
          buttonOptions: {
              symbolStroke: '#DDDDDD',
              theme: {
                  fill: '#505053'
              }
          }
      },
      // scroll charts
      rangeSelector: {
          buttonTheme: {
              fill: '#505053',
              stroke: '#000000',
              style: {
                  color: '#CCC'
              },
              states: {
                  hover: {
                      fill: '#707073',
                      stroke: '#000000',
                      style: {
                          color: 'white'
                      }
                  },
                  select: {
                      fill: '#000003',
                      stroke: '#000000',
                      style: {
                          color: 'white'
                      }
                  }
              }
          },
          inputBoxBorderColor: '#505053',
          inputStyle: {
              backgroundColor: '#333',
              color: 'silver'
          },
          labelStyle: {
              color: 'silver'
          }
      },
      navigator: {
          handles: {
              backgroundColor: '#666',
              borderColor: '#AAA'
          },
          outlineColor: '#CCC',
          maskFill: 'rgba(255,255,255,0.1)',
          series: {
              color: '#7798BF',
              lineColor: '#A6C7ED'
          },
          xAxis: {
              gridLineColor: '#505053'
          }
      },
      scrollbar: {
          barBackgroundColor: '#808083',
          barBorderColor: '#808083',
          buttonArrowColor: '#CCC',
          buttonBackgroundColor: '#606063',
          buttonBorderColor: '#606063',
          rifleColor: '#FFF',
          trackBackgroundColor: '#404043',
          trackBorderColor: '#404043'
      }
  };

Highcharts.themes.transparent = {
      colors: ['white', '#2b908f', '#90ee7e', '#f45b5b', '#7798BF', '#aaeeee', '#ff0066',
          '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
      chart: {
          backgroundColor: {
              linearGradient: { x1: 0, y1: 0, x2: 1, y2: 1 },
              stops: [
                  [0, '#2a2a2b'],
                  [1, '#3e3e40']
              ]
          },
          style: {
              fontFamily: '\'Unica One\', sans-serif'
          },
          plotBorderColor: '#606063'
      },
      title: {
          style: {
              color: 'white',
             //textTransform: 'uppercase',
              fontSize: titleFontSize
          }
      },
      subtitle: {
          style: {
              color: 'white',
             //textTransform: 'uppercase',
              fontSize: subtitleFontSize
          }
      },
      xAxis: {
          gridLineDashStyle: 'longdash',
          gridLineColor: 'rgba(255,255,255, 0.1)',
          labels: {
              style: {
                  color: 'white',
                  fontSize: xAxisLabelFontSize
              }
          },
          lineColor: 'rgba(255,255,255, 0.1)',
          minorGridLineColor: 'rgba(255,255,255, 0.1)',
          tickColor: 'rgba(255,255,255, 0.1)',
          title: {
              style: {
                  color: 'white',
                  fontSize: xAxisTitleFontSize
              }
          }
      },
      yAxis: {
          gridLineDashStyle: 'longdash',
          gridLineColor: 'rgba(255,255,255, 0.1)',
          labels: {
              style: {
                  color: 'white',
                  fontSize: yAxisFontSize // Keep small or it squishes mobile
              }
          },
          lineColor: 'rgba(255,255,255, 0.1)',
          minorGridLineColor: 'rgba(255,255,255, 0.1)',
          tickColor: 'rgba(255,255,255, 0.1)',
          tickWidth: 1,
          title: {
              style: {
                  color: 'white',
                  fontSize: yAxisFontSize // Keep small or it squishes mobile
              }
          }
      },
      tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.85)',
          style: {
              color: '#F0F0F0',
              fontSize: tooltipFontSize
          }
      },
      plotOptions: {
          series: {
              dataLabels: {
                  enabled: false,
                  color: 'white',
                  style: {
                      fontSize: dataLabelFontSize
                  }
              },
              marker: {
                  lineColor: '#333'
              }
          },
          boxplot: {
              fillColor: '#505053'
          },
          candlestick: {
              lineColor: 'white'
          },
          errorbar: {
              color: 'white'
          }
      },
      legend: {
          //backgroundColor: 'rgba(0, 0, 0, 0.5)',
          itemStyle: {
              color: 'white'
          },
          itemHoverStyle: {
              color: '#FFF'
          },
          itemHiddenStyle: {
              color: '#606063'
          },
          title: {
              style: {
                  color: '#C0C0C0'
              }
          }
      },
      credits: {
          style: {
              color: 'white'
          }
      },
      labels: {
          style: {
              color: 'white'
          }
      },
      drilldown: {
          activeAxisLabelStyle: {
              color: 'white'
          },
          activeDataLabelStyle: {
              color: 'white'
          }
      },
      navigation: {
          buttonOptions: {
              symbolStroke: '#DDDDDD',
              theme: {
                  fill: 'rgba(0,0,0,0)'
              }
          },
          menuItemStyle: {
              fontSize: highstockNavigatorFontSize
          }
      },
      // scroll charts
      rangeSelector: {
          buttons: [{
              type: 'week',
              count: 1,
              text: '1w'
          }, {
              type: 'month',
              count: 1,
              text: '1m'
          }, {
              type: 'month',
              count: 3,
              text: '3m'
          }, {
              type: 'month',
              count: 6,
              text: '6m'
          }, {
              type: 'ytd',
              text: 'YTD'
          }, {
              type: 'year',
              count: 1,
              text: '1y'
          }, {
              type: 'all',
              text: 'All'
          }],
          //selected: 0,
          buttonTheme: {
              fill: 'rgba(0,0,0,0)',
              stroke: 'rgba(0,0,0,0)',
              style: {
                  color: 'white',
                  fontSize: xAxisTitleFontSize
              },
              states: {
                  hover: {
                      fill: 'rgba(0,0,0,0)',
                      stroke: 'white',
                      style: {
                          color: 'white',
                          border: '2px solid white',
                          fontSize: largeButtonFontSize
                      }
                  },
                  select: {
                      fill: 'rgba(0,0,0,0)',
                      stroke: 'white',
                      style: {
                          border: '2px solid white',
                          color: 'white',
                          fontSize: largeButtonFontSize
                      }
                  }
              }
          },
          inputEnabled: false,
          inputBoxBorderColor: 'rgba(0,0,0,0)',
          inputStyle: {
              backgroundColor: '#333',
              color: 'white'
          },
          labelStyle: {
              color: 'white'
          }
      },
      navigator: {
          handles: {
              backgroundColor: 'white',
              borderColor: '#AAA'
          },
          outlineColor: '#CCC',
          maskFill: 'rgba(255,255,255,0.1)',
          series: {
              color: 'white',
              lineColor: 'white'
          },
          xAxis: {
              gridLineColor: 'white',
              tickWidth: 0,
              lineWidth: 0,
              gridLineWidth: 1,
              tickPixelInterval: 200,
              labels: {
                  align: 'left',
                  style: {
                      color: 'white',
                      fontSize: highstockNavigatorFontSize
                  },
                  x: 3,
                  y: -4
              }
          }
      },
      scrollbar: {
          barBackgroundColor: 'white',
          barBorderColor: 'white',
          buttonArrowColor: '#CCC',
          buttonBackgroundColor: 'white',
          buttonBorderColor: 'white',
          rifleColor: 'white',
          trackBackgroundColor: 'white',
          trackBorderColor: 'white'
      }
  };

    Highcharts.themes.white = {
        colors: ['white', '#2b908f', '#90ee7e', '#f45b5b', '#7798BF', '#aaeeee', '#ff0066',
            '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
        chart: {
            backgroundColor: 'white',
            style: {
                //fontFamily: '\'Unica One\', sans-serif'
            },
            plotBorderColor: '#606063'
        },
        title: {
            style: {
                color: 'black',
               //textTransform: 'uppercase',
                fontSize: titleFontSize
            }
        },
        subtitle: {
            style: {
                color: 'black',
               //textTransform: 'uppercase',
                fontSize: subtitleFontSize
            }
        },
        xAxis: {
            gridLineDashStyle: 'longdash',
            gridLineColor: 'rgba(255,255,255, 0.1)',
            labels: {
                style: {
                    color: 'black',
                    fontSize: xAxisLabelFontSize
                }
            },
            lineColor: 'rgba(255,255,255, 0.1)',
            minorGridLineColor: 'rgba(255,255,255, 0.1)',
            tickColor: 'rgba(255,255,255, 0.1)',
            title: {
                style: {
                    color: 'black',
                    fontSize: xAxisTitleFontSize
                }
            }
        },
        yAxis: {
            gridLineDashStyle: 'longdash',
            gridLineColor: 'rgba(255,255,255, 0.1)',
            labels: {
                style: {
                    color: 'black',
                    fontSize: yAxisFontSize // Keep small or it squishes mobile
                }
            },
            lineColor: 'rgba(255,255,255, 0.1)',
            minorGridLineColor: 'rgba(255,255,255, 0.1)',
            tickColor: 'rgba(255,255,255, 0.1)',
            tickWidth: 1,
            title: {
                style: {
                    color: 'black',
                    fontSize: yAxisFontSize // Keep small or it squishes mobile
                }
            }
        },
        tooltip: {
            backgroundColor: 'white',
            style: {
                color: 'black',
                fontSize: tooltipFontSize
            }
        },
        plotOptions: {
            series: {
                dataLabels: {
                    enabled: false,
                    color: 'black',
                    style: {
                        fontSize: dataLabelFontSize
                    }
                },
                marker: {
                    lineColor: '#333'
                }
            },
            boxplot: {
                fillColor: '#505053'
            },
            candlestick: {
                lineColor: 'black'
            },
            errorbar: {
                color: 'black'
            }
        },
        legend: {
            //backgroundColor: 'rgba(0, 0, 0, 0.5)',
            itemStyle: {
                color: 'black'
            },
            itemHoverStyle: {
                //color: '#FFF'
            },
            itemHiddenStyle: {
                color: '#606063'
            },
            title: {
                style: {
                    color: '#C0C0C0'
                }
            }
        },
        credits: {
            style: {
                color: 'black'
            }
        },
        labels: {
            style: {
                color: 'black'
            }
        },
        drilldown: {
            activeAxisLabelStyle: {
                color: 'black'
            },
            activeDataLabelStyle: {
                color: 'black'
            }
        },
        navigation: {
            buttonOptions: {
                symbolStroke: '#DDDDDD',
                theme: {
                    fill: 'rgba(0,0,0,0)'
                }
            },
            menuItemStyle: {
                fontSize: highstockNavigatorFontSize
            }
        },
        // scroll charts
        rangeSelector: {
            buttons: [{
                type: 'week',
                count: 1,
                text: '1w'
            }, {
                type: 'month',
                count: 1,
                text: '1m'
            }, {
                type: 'month',
                count: 3,
                text: '3m'
            }, {
                type: 'month',
                count: 6,
                text: '6m'
            }, {
                type: 'ytd',
                text: 'YTD'
            }, {
                type: 'year',
                count: 1,
                text: '1y'
            }, {
                type: 'all',
                text: 'All'
            }],
            //selected: 0,
            buttonTheme: {
                fill: 'rgba(0,0,0,0)',
                stroke: 'rgba(0,0,0,0)',
                style: {
                    color: 'black',
                    fontSize: xAxisTitleFontSize
                },
                states: {
                    hover: {
                        //fill: 'rgba(0,0,0,0)',
                        //stroke: 'white',
                        style: {
                            color: 'black',
                            //border: '2px solid white',
                            fontSize: largeButtonFontSize
                        }
                    },
                    select: {
                        fill: 'rgba(0,0,0,0)',
                        //stroke: 'white',
                        style: {
                            border: '2px solid white',
                            color: 'black',
                            fontSize: largeButtonFontSize
                        }
                    }
                }
            },
            inputEnabled: false,
            //inputBoxBorderColor: 'rgba(0,0,0,0)',
            inputStyle: {
                //backgroundColor: '#333',
                //color: 'white'
            },
            labelStyle: {
                //color: 'white'
            }
        },
        navigator: {
            handles: {
                //backgroundColor: 'black',
                //borderColor: '#AAA'
            },
            //outlineColor: '#CCC',
            //maskFill: 'rgba(255,255,255,0.1)',
            // series: {
            //     color: 'black',
            //     lineColor: 'black'
            // },
            xAxis: {
                gridLineColor: 'black',
                tickWidth: 0,
                lineWidth: 0,
                gridLineWidth: 1,
                tickPixelInterval: 200,
                labels: {
                    align: 'left',
                    style: {
                        color: 'black',
                        fontSize: highstockNavigatorFontSize
                    },
                    x: 3,
                    y: -4
                }
            }
        },
        // scrollbar: {
        //     barBackgroundColor: 'white',
        //     barBorderColor: 'white',
        //     buttonArrowColor: '#CCC',
        //     buttonBackgroundColor: 'white',
        //     buttonBorderColor: 'white',
        //     rifleColor: 'white',
        //     trackBackgroundColor: 'white',
        //     trackBorderColor: 'white'
        // }
    };

    Highcharts.themes._defaults = {
    chart: {
      style: {
        fontFamily: 'Helvetica',
        fontWeight: 'normal'
      }
    },
    xAxis: {
      lineColor: '#ccc'
    },
    yAxis: {
      gridLineColor: '#e0e0e0'
    },
    credits: false,
    legend: {
      borderRadius: 0,
      borderWidth: 0,
      align: 'center',
      x: 15
    }
  };

