;(function ($) {

    $(document).ready(function () {

        //create days array for chart
        var provideArray = [];

        var counter = 0;

        $('.ns_day').each(function () {
            var object = {};
            object.date = $(this).attr('data_day');
            object.users = $($('.ns_users')[counter]).attr('data_users');
            object.revenue= $($('.ns_sales')[counter]).attr('data-sales');
            provideArray.push(object);
            counter++;
        });

        var chart = AmCharts.makeChart("chartdiv", {
            "type": "serial",
            "theme": "light",
            "balloon": {
                "adjustBorderColor": true,
                "color": "#ffffff",
                "fillColor": "#333333"
            },
            "legend": {
                // "equalWidths": false,
                // "useGraphSettings": true,
                // "valueAlign": "left",
                // "valueWidth": 10,
                // "position": "top",
                // "fontSize": 12,
                // "useMarkerColorForLabels": true,
                // // "periodValueText": "[[value]]",
                // "valueText": "[[close]]"
                "enabled": false
            },
            "dataProvider": provideArray,
            "columnSpacing": 0.1,
            "valueAxes": [{
                "id": "usersAxis",
                "axisAlpha": 0,
                "gridAlpha": 0,
                "labelsEnabled": true,
                "position": "left",
                "title": "Users",
                "gridThickness": 0,
                "color": "#f1c40e",
                "titleColor": "#f1c40e",
                "titleBold": false
            }, {
                "id": "volumeAxis",
                "axisAlpha": 0,
                "gridAlpha": 0,
                "labelsEnabled": true,
                "position": "right",
                "title": "Revenue",
                "gridThickness": 0,
                "color": "#9dd700",
                "titleColor": "#9dd700",
                "titleBold": false,
                "unit" :"$",
                "unitPosition" : "left"
            }],
            "graphs": [
                {
                    "title": "Visitors",
                    "type": "column",
                    "valueField": "users",
                    "fillAlphas": 1,
                    "valueAxis": "usersAxis",
                    "balloonText": "[[value]] Visits",
                    "fillColors": "#f1c40e",
                    "labelColorField": "#f1c40e",
                    "lineColor": "#000000",
                    "labelPosition": "top",
                    "lineAlpha": 1,
                    "lineThickness": 0
                },
                {
                    "id": "volumeAxis",
                    "title": "Sales",
                    "type": "column",
                    "valueField": "revenue",
                    "fillAlphas": 1,
                    "valueAxis": "volumeAxis",
                    "balloonText": "[[value]]$",
                    "fillColors": "#9dd700",
                    "labelColorField": "#9dd700",
                    "lineColor": "#9dd700",
                    "lineThickness": 0,
                }
            ],
            // "graphs": [{
            //     "alphaField": "alpha",
            //     "balloonText": "[[value]] miles",
            //     "dashLengthField": "dashLength",
            //     "fillAlphas": 0.7,
            //     "legendPeriodValueText": "total: [[value.sum]] mi",
            //     "legendValueText": "[[value]] mi",
            //     "title": "Users",
            //     "type": "column",
            //     "valueField": "Users",
            //     "valueAxis": "usersAxis"
            // }, {
            //     "balloonText": "[[value]]%",
            //     "dashLengthField": "dashLength",
            //     "descriptionField": "townName",
            //     "labelPosition": "right",
            //     "labelText": "[[townName2]]",
            //     "legendValueText": "[[value]]/[[description]]",
            //     "title": "Volume",
            //     "type": "column",
            //     "fillAlphas": 0,
            //     "valueField": "Volume",
            //     "valueAxis": "volumeAxis"
            // }],
            "chartCursor": {
                "categoryBalloonDateFormat": "DD",
                "cursorAlpha": 0,
                "cursorColor": "#333333",
                "fullWidth": true,
                "valueBalloonsEnabled": false,
                "zoomable": false
            },
            "dataDateFormat": "YYYY-MM-DD",
            "categoryField": "date",
            "categoryAxis": {

                "dateFormats": [{
                    "period": "DD",
                    "format": "DD"
                }, {
                    "period": "WW",
                    "format": "MMM DD"
                }, {
                    "period": "MM",
                    "format": "MMM"
                }, {
                    "period": "YYYY",
                    "format": "YYYY"
                }],
                "showFirstLabel": false,
                "parseDates": true,
                "autoGridCount": false,
                "axisColor": "#333333",
                "color": "#333333",
                "gridAlpha": 0,
                "gridColor": "#333333",
                "gridCount": 5,
                "gridThickness": 1,
                "gridPosition": "start",
                "boldPeriodBeginning": false
            },
            "export": {
                "enabled": false
            }
        });


        // $('.ns_day').each(function(){
        //
        //     DaysInMonth.push($(this).attr('data_day'));
        // });
        // $('.ns_users').each(function(){
        //     object['distance'] = $(this).attr('data_users');
        //     UsersPerDay.push($(this).attr('data_users'));
        // });
        // $('.ns_sales').each(function(){
        //     object['latitude'] = $(this).attr('data-sales');
        //     RevenuePerDay.push($(this).attr('data-sales'));
        // });


        // DaysInMonth = [1,2,3,4,5,6,7,8,9,10];
        // UsersPerDay = [12,10,3,20,16,32,25,19,32,22];
        // RevenuePerDay = [320,86,1024,100,255,745,86,77,92,0];

    });

}(jQuery));