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
