// initSpiderWeb();

function initColChart(diseaseSorted){
    var commonDiseaseNameList = [];
    var commonDiseaseNumList = [];

    diseaseSorted.forEach(function(dict){
      commonDiseaseNameList.push(dict[0]);
      commonDiseaseNumList.push(dict[1]);
    });

    $(function () {
        $('#column-plot').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: 'Most Common Disease in Three Months'
            },
            subtitle: {
                text: 'Source: TeamEMR'
            },
            xAxis: {
                categories: commonDiseaseNameList,
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Number of occurence'
                }
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [{
                name: commonDiseaseNameList[0],
                data: [commonDiseaseNumList[0]]

            }, {
              name: commonDiseaseNameList[1],
              data: [commonDiseaseNumList[1]]
            }, {
              name: commonDiseaseNameList[2],
              data: [commonDiseaseNumList[2]]
            }, {
              name: commonDiseaseNameList[3],
              data: [commonDiseaseNumList[3]]
            }, {
              name: commonDiseaseNameList[4],
              data: [commonDiseaseNumList[4]]
            }]
        });
    });
}


function parseDOB(dobList){
  var curYear = new Date().getFullYear();

  var ageGroup = [];
  // 0 ~ 18, 19~ 49, 50 ~64, 64+
  ageGroup['children'] = [];
  ageGroup['growUp'] = [];
  ageGroup['middleAge'] = [];
  ageGroup['elders'] = [];

  dobList.forEach(function(dob){
    var bornYear = parseInt(dob.split('-')[0]);
    var age = curYear - bornYear;

    if(age >= 64){
      ageGroup['elders'].push(age);
    }else if (age >=50) {
      ageGroup['middleAge'].push(age);
    }else if (age >= 19){
      ageGroup['growUp'].push(age);
    }else{
      ageGroup['children'].push(age);
    }
  });
  return ageGroup;
}

// ageGroupList is an object with three array,
// with names 'children', 'middleAge', 'growUp', and 'elders'.
function initPieChart(ageGroupList){
    $(function () {


        $('#pie-plot').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Different Age Groups of Patients'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        }
                    }
                }
            },
            series: [{
                name: 'Age Group',
                colorByPoint: true,
                data: [{
                    name: 'Children and Teenagers <br> (0~18)<br>',
                    y: ageGroupList['children'].length,
                }, {
                    name: 'Young and Middle Age Adults<br>(19 ~ 49)<br>',
                    y: ageGroupList['growUp'].length,
                    sliced: true,
                    selected: true
                }, {
                    name: '50 ~ 64 Years Old<br>',
                    y:  ageGroupList['middleAge'].length,
                }, {
                    name:  'Elders <br> (64+)<br>',
                    y: ageGroupList['elders'].length,
                },]
            }]
        });
    });
}



function parseMonthlyVisits(visitData){
  var allVisits = [];
  var newVisits = [];
  var patientIdSet =  new Set();

  // init vists and newVisits
  for(var i = 0; i < 12; i++){
    allVisits[i] = 0;
    newVisits[i] = 0;
  }

  visitData.forEach(function(visit){
    var visitYear = parseInt(visit['date'].split('-')[0]);
    if(visitYear == new Date().getFullYear()){
      var visitMonth = parseInt(visit['date'].split('-')[1]);
      var curPatientId = visit['p_id'];

      if(!patientIdSet.has(curPatientId)){
        patientIdSet.add(curPatientId);
        newVisits[visitMonth+1] += 1;
     }

      allVisits[visitMonth+1] += 1;
    }
  });

  return {
    allVisits: allVisits,
    newVisits: newVisits
    };
}

function initMonthlyVisit(visitData){
  $(function () {
    Highcharts.chart('monthly-visit-plot', {
        chart: {
            type: 'line'
        },
        title: {
            text: 'Monthly Patient Visits'
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        },
        yAxis: {
            title: {
                text: 'Number of Patients'
            }
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: false
            }
        },
        series: [{
            name: 'All Visits',
            data: visitData.allVisits
        }, {
            name: 'New Visits',
            data: visitData.newVisits
        }]
    });
});
}

function initSpiderWeb(visitData) {

    $(function () {
        $('#spider-web').highcharts({
            chart: {
                polar: true,
                type: 'line'
            },

            title: {
                text: 'Patient vs Local Average ',
                x: -80
            },

            pane: {
                size: '80%'
            },

            xAxis: {
                categories: ['Weight', 'Blood Pressure High', 'Blood Pressure Low',
                    'Temperature', 'Respiratory Rate',
                ],
                tickmarkPlacement: 'on',
                lineWidth: 0
            },

            yAxis: {
                gridLineInterpolation: 'polygon',
                lineWidth: 0,
                min: 0
            },

            tooltip: {
                shared: true,
                pointFormat: '<span style="color:{series.color}">{series.name}: <b>{point.y:,.0f}</b><br/>'
            },

            legend: {
                align: 'right',
                verticalAlign: 'top',
                y: 70,
                layout: 'vertical'
            },

            series: [{
                name: 'Patient',
                data: visitData,
                pointPlacement: 'on'
            }, {
                name: ' Local Average',
                data: [83, 120, 90, 28, 73],
                pointPlacement: 'on'
            }]

        });
    });
}

function parseDiseasData(localVisitList){
  var curMonth = new Date().getMonth();
  var curYear = new Date().getFullYear();

  var diseasesData = [];
  var diseasesSet = new Set();

  // get list of diseases in recent month
  localVisitList.forEach(function(visit){
    var year = parseInt(visit['date'].split('-')[0]);
    var month = parseInt(visit['date'].split('-')[1]);

    if(year == curYear){
      // only use data from recent months
      if(month >= curMonth -2){
        var curDiseasesList = visit['diagnosis'].split(',');

        curDiseasesList.forEach(function(_disease){
          if(_disease.length > 1){
            if(diseasesSet.has(_disease)){
              diseasesData[_disease] += 1;
            }else{
              diseasesSet.add(_disease);
              diseasesData[_disease] = 1;
            }
          }
        });

      }
    }
  });
  // sort dictionary by values
  // diseasesData = Object.keys(diseasesData).sort(function(a,b){return diseasesData[a]-diseasesData[b]})
  var sortDisease = [];
  for (var dis in diseasesData)
      sortDisease.push([dis, diseasesData[dis]])

  sortDisease.sort(function(a, b) {
      return a[1] - b[1]
  })

  var topFiveDisease = [];
  for(var i = 4; i > -1; i--){
    topFiveDisease.push(sortDisease[sortDisease.length- 5 + i]);
  }

  return topFiveDisease;
}

function parseScatterVisit(localVisitList){
  var overWeighted = [];
  var normalWeighted = [];

  localVisitList.forEach(function(visit){
    var curWeight = parseInt( visit['weight']);
    var curBPH = parseInt(visit['bph']);
    var curResRate = parseInt(visit['respiratory_rate']);

    if(curWeight >=80){
      overWeighted.push([curResRate, curBPH]);
    }else{
      normalWeighted.push([curResRate, curBPH]);
    }
  });


  return {
    overWeighted: overWeighted,
    normal: normalWeighted
  };
}

function initScatterPlot(parsedVisit) {
  var overWeighted = parsedVisit['overWeighted'];
  var normalWeighted = parsedVisit['normal'];

    $(function () {
        $('#scatter-plot').highcharts({
            chart: {
                type: 'scatter',
                zoomType: 'xy'
            },
            title: {
                text: 'Breathing rate and blood pressure for overweighted and normal local patients '
            },
            subtitle: {
                // text: 'Source: Heinz  2003'
            },
            xAxis: {
                title: {
                    enabled: true,
                    text: 'Breathing Rate'
                },
                startOnTick: true,
                endOnTick: true,
                showLastLabel: true
            },
            yAxis: {
                title: {
                    text: 'Blood Pressure High'
                }
            },
            legend: {
                layout: 'vertical',
                align: 'left',
                verticalAlign: 'top',
                x: 100,
                y: 70,
                floating: true,
                backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF',
                borderWidth: 1
            },
            plotOptions: {
                scatter: {
                    marker: {
                        radius: 5,
                        states: {
                            hover: {
                                enabled: true,
                                lineColor: 'rgb(100,100,100)'
                            }
                        }
                    },
                    states: {
                        hover: {
                            marker: {
                                enabled: false
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<b>{series.name}</b><br>',
                        pointFormat: '{point.x} cm, {point.y} kg'
                    }
                }
            },
            series: [{
                name: 'Overweighted',
                color: 'rgba(223, 83, 83, .5)',
                data: overWeighted

            }, {
                name: 'Normal Patients',
                color: 'rgba(119, 152, 191, .5)',
                data: normalWeighted
            }]
        });
    });

}

function initPressureHist() {
    var ranges = [

            [1248134400000, 90, 120],
            [1248220800000, 90, 120],
            [1248307200000, 90, 120],
            [1248393600000, 90, 120],
      ],
        averages = [
            [1248134400000, 103.7],
            [1248220800000, 115.7],
            [1248307200000, 104.6],
            [1248393600000, 100.3],

        ];


    $('#pressure-hist-range').highcharts({

        title: {
            text: 'Blood Pressure'
        },

        xAxis: {
            type: 'datetime'
        },

        yAxis: {
            title: {
                text: null
            }
        },

        tooltip: {
            crosshairs: true,
            shared: true,
            valueSuffix: ''
        },

        legend: {},

        series: [{
            name: 'Blood Pressure',
            data: averages,
            zIndex: 1,
            marker: {
                fillColor: 'white',
                lineWidth: 2,
                lineColor: Highcharts.getOptions().colors[0]
            }
        }, {
            name: 'Range',
            data: ranges,
            type: 'arearange',
            lineWidth: 0,
            linkedTo: ':previous',
            color: Highcharts.getOptions().colors[0],
            fillOpacity: 0.3,
            zIndex: 0
        }]
    });
}

function initDummyScatterPlot(){
  $(function () {
      $('#scatter-dummy-plot').highcharts({
          chart: {
              type: 'scatter',
              zoomType: 'xy'
          },
          title: {
              text: 'Cholesterol and BMI of 507 Individuals with and without medicine'
          },
          subtitle: {
              // text: 'Source: Heinz  2003'
          },
          xAxis: {
              title: {
                  enabled: true,
                  text: 'BMI'
              },
              startOnTick: true,
              endOnTick: true,
              showLastLabel: true
          },
          yAxis: {
              title: {
                  text: 'Cholesterol'
              }
          },
          legend: {
              layout: 'vertical',
              align: 'left',
              verticalAlign: 'top',
              x: 100,
              y: 70,
              floating: true,
              backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF',
              borderWidth: 1
          },
          plotOptions: {
              scatter: {
                  marker: {
                      radius: 5,
                      states: {
                          hover: {
                              enabled: true,
                              lineColor: 'rgb(100,100,100)'
                          }
                      }
                  },
                  states: {
                      hover: {
                          marker: {
                              enabled: false
                          }
                      }
                  },
                  tooltip: {
                      headerFormat: '<b>{series.name}</b><br>',
                      pointFormat: '{point.x} cm, {point.y} kg'
                  }
              }
          },
          series: [{
              name: 'With Medicine',
              color: 'rgba(223, 83, 83, .5)',
              data: [
                  [161.2, 51.6],
                  [167.5, 59.0],
                  [159.5, 49.2],
                  [157.0, 63.0],
                  [155.8, 53.6],
                  [170.0, 59.0],
                  [159.1, 47.6],
                  [166.0, 69.8],
                  [176.2, 66.8],
                  [160.2, 75.2],
                  [172.5, 55.2],
                  [170.9, 54.2],
                  [172.9, 62.5],
                  [153.4, 42.0],
                  [160.0, 50.0],
                  [147.2, 49.8],
                  [168.2, 49.2],
                  [175.0, 73.2],
                  [157.0, 47.8],
                  [167.6, 68.8],
                  [159.5, 50.6],
                  [175.0, 82.5],
                  [166.8, 57.2],
                  [176.5, 87.8],
                  [170.2, 72.8],
                  [174.0, 54.5],
                  [173.0, 59.8],
                  [179.9, 67.3],
                  [170.5, 67.8],
                  [160.0, 47.0],
                  [154.4, 46.2],
                  [162.0, 55.0],
                  [176.5, 83.0],
                  [160.0, 54.4],
                  [152.0, 45.8],
                  [162.1, 53.6],
                  [170.0, 73.2],
                  [160.2, 52.1],
                  [161.3, 67.9],
                  [166.4, 56.6],
                  [168.9, 62.3],
                  [163.8, 58.5],
                  [167.6, 54.5],
                  [160.0, 50.2],
                  [161.3, 60.3],
                  [167.6, 58.3],
                  [165.1, 56.2],
                  [160.0, 50.2],
                  [170.0, 72.9],
                  [157.5, 59.8],
                  [167.6, 61.0],
                  [160.7, 69.1],
                  [163.2, 55.9],
                  [152.4, 46.5],
                  [157.5, 54.3],
                  [168.3, 54.8],
                  [180.3, 60.7],
                  [165.5, 60.0],
                  [165.0, 62.0],
                  [160.0, 75.5],
                  [172.7, 68.2],
                  [162.6, 61.4],
                  [157.5, 76.8],
                  [176.5, 71.8],
                  [164.4, 55.5],
                  [160.7, 48.6],
                  [174.0, 66.4],
                  [163.8, 67.3]
              ]

          }, {
              name: 'Without Medicine',
              color: 'rgba(119, 152, 191, .5)',
              data: [
                  [174.0, 65.6],
                  [175.3, 71.8],
                  [193.5, 80.7],
                  [186.5, 72.6],
                  [187.2, 78.8],
                  [181.5, 74.8],
                  [184.0, 86.4],
                  [184.5, 78.4],
                  [175.0, 62.0],
                  [184.0, 81.6],
                  [180.0, 76.6],
                  [177.8, 83.6],
                  [192.0, 90.0],
                  [176.0, 74.6],
                  [174.0, 71.0],
                  [184.0, 79.6],
                  [192.7, 93.8],
                  [171.5, 70.0],
                  [173.0, 72.4],
                  [176.0, 85.9],
                  [176.0, 78.8],
                  [180.5, 77.8],
                  [172.7, 66.2],
                  [176.0, 86.4],
                  [173.5, 81.8],
                  [178.0, 89.6],
                  [180.3, 82.8],
                  [180.3, 76.4],
                  [164.5, 63.2],
                  [173.0, 60.9],
                  [183.5, 74.8],
                  [175.5, 70.0],
                  [188.0, 72.4],
                  [189.2, 84.1],
                  [172.8, 69.1],
                  [170.0, 59.5],
                  [182.0, 67.2],
                  [170.0, 61.3],
                  [177.8, 68.6],
                  [184.2, 80.1],
                  [186.7, 87.8],
                  [171.4, 84.7],
                  [172.7, 73.4],
                  [175.3, 72.1],
                  [180.3, 82.6],
                  [182.9, 88.7],
                  [188.0, 84.1],
                  [177.2, 94.1],
                  [172.1, 74.9],
                  [167.0, 59.1],
                  [169.5, 75.6],
                  [174.0, 86.2],
                  [172.7, 75.3],
                  [182.2, 87.1],
                  [164.1, 55.2],
                  [163.0, 57.0],
                  [171.5, 61.4],
                  [184.2, 76.8],
                  [174.0, 86.8],
                  [174.0, 72.2],
                  [175.5, 70.9],
                  [180.6, 72.5],
                  [177.0, 72.5],
                  [177.1, 83.4],
                  [181.6, 75.5],
                  [176.5, 73.0],
                  [175.0, 70.2],
                  [174.0, 73.4],
                  [165.1, 70.5],
                  [177.0, 68.9],
                  [192.0, 102.3],
                  [176.5, 68.4],
                  [169.4, 65.9],
                  [182.1, 75.7],
                  [179.8, 84.5],
                  [175.3, 87.7],
                  [184.9, 86.4],
                  [177.3, 73.2],
                  [167.4, 53.9],
                  [178.1, 72.0],
                  [168.9, 55.5],
                  [157.2, 58.4],
                  [180.3, 83.2],
                  [170.2, 72.7],
                  [177.8, 64.1],
                  [172.7, 72.3],
                  [165.1, 65.0],
                  [186.7, 86.4],
                  [165.1, 65.0],
                  [174.0, 88.6],
                  [175.3, 84.1],
                  [185.4, 66.8],
                  [177.8, 75.5],
                  [180.3, 93.2],
                  [180.3, 82.7],
                  [177.8, 58.0],
                  [177.8, 79.5],
                  [177.8, 78.6],
                  [177.8, 71.8],
                  [177.8, 116.4],
                  [163.8, 72.2],
                  [188.0, 83.6],
                  [198.1, 85.5],
                  [175.3, 90.9],
                  [166.4, 85.9],
                  [190.5, 89.1],
                  [166.4, 75.0],
                  [177.8, 77.7],
                  [179.7, 86.4],
                  [172.7, 90.9],
                  [190.5, 73.6],
                  [185.4, 76.4],
                  [168.9, 69.1],
                  [167.6, 84.5],
                  [175.3, 64.5],
                  [170.2, 69.1],
                  [190.5, 108.6],
                  [177.8, 86.4],
                  [190.5, 80.9],
                  [177.8, 87.7],
                  [184.2, 94.5],
                  [176.5, 80.2],
                  [177.8, 72.0],
                  [190.5, 98.2],
                  [177.8, 84.1],
                  [180.3, 83.2],
                  [180.3, 83.2]
              ]
          }]
      });
  });

}
