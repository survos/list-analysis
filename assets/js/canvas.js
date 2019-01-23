// require ('canvasjs');

//Better to construct options first and then pass it as a parameter
var options = {
    animationEnabled: true,
    title:{
        text: "Coal Reserves of Countries"
    },
    axisY:{
        title:"Coal (mn tonnes)"
    },
    toolTip: {
        shared: true,
        reversed: true
    },
    data: [{
        type: "stackedColumn",
        name: "Anthracite and Bituminous",
        showInLegend: "true",
        yValueFormatString: "#,##0mn tonnes",
        dataPoints: [
            { y: 111338 , label: "USA" },
            { y: 49088, label: "Russia" },
            { y: 62200, label: "China" },
            { y: 90085, label: "India" },
            { y: 38600, label: "Australia" },
            { y: 48750, label: "SA" }
        ]
    },
        {
            type: "stackedColumn",
            name: "SubBituminous and Lignite",
            showInLegend: "true",
            yValueFormatString: "#,##0mn tonnes",
            dataPoints: [
                { y: 135305 , label: "USA" },
                { y: 107922, label: "Russia" },
                { y: 52300, label: "China" },
                { y: 3360, label: "India" },
                { y: 39900, label: "Australia" },
                { y: 0, label: "SA" }
            ]
        }]
};

$("#chartContainer").CanvasJSChart(options);