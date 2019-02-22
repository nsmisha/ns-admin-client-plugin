/**
 * Created by Never Settle on 15.11.2017.
 */
"use strict";
if (gadwpItemData.mapsApiKey) {
    google.charts.load("current", {
        mapsApiKey: gadwpItemData.mapsApiKey,
        packages: ["corechart", "table", "orgchart", "geochart"]
    })
} else {
    google.charts.load("current", {
        packages: ["corechart", "table", "orgchart", "geochart"]
    })
}
google.charts.setOnLoadCallback(GADWPReportLoad);
gadwpItemData.getID = function(a) {
    if (gadwpItemData.scope == "admin-item") {
        if (typeof a.id == "undefined") {
            return 0
        }
        if (a.id.split("-")[1] == "undefined") {
            return 0
        } else {
            return a.id.split("-")[1]
        }
    } else {
        if (typeof a.id == "undefined") {
            return 1
        }
        if (a.id.split("-")[4] == "undefined") {
            return 1
        } else {
            return a.id.split("-")[4]
        }
    }
};
gadwpItemData.getSelector = function(a) {
    if (a == "admin-item") {
        return 'a[id^="gadwp-"]'
    } else {
        return 'li[id^="wp-admin-bar-gadwp"] a'
    }
};
gadwpItemData.responsiveDialog = function() {
    var b, a, c;
    c = jQuery(".ui-dialog:visible");
    c.each(function() {
        b = jQuery(this).find(".ui-dialog-content").data("ui-dialog");
        if (b.options.fluid) {
            a = jQuery(window).width();
            if (a < (parseInt(b.options.maxWidth) + 50)) {
                jQuery(this).css("max-width", "90%")
            } else {
                jQuery(this).css("max-width", b.options.maxWidth + "px")
            }
            b.option("position", b.options.position)
        }
    })
};
jQuery.fn.extend({
    gadwpItemReport: function(g) {
        var a, d, h, e, f, i, c, b = "-" + g;
        d = {
            setCookie: function(k, m) {
                var j, l = new Date();
                if (gadwpItemData.scope == "admin-widgets") {
                    k = "gadwp_wg_" + k
                } else {
                    k = "gadwp_ir_" + k
                }
                l.setTime(l.getTime() + (24 * 60 * 60 * 1000 * 365));
                j = "expires=" + l.toUTCString();
                document.cookie = k + "=" + m + "; " + j + "; path=/"
            },
            getCookie: function(j) {
                var l, m, n, k = 0;
                if (gadwpItemData.scope == "admin-widgets") {
                    j = "gadwp_wg_" + j + "="
                } else {
                    j = "gadwp_ir_" + j + "="
                }
                m = document.cookie.split(";");
                for (k = 0; k < m.length; k++) {
                    l = m[k];
                    while (l.charAt(0) == " ") {
                        l = l.substring(1)
                    }
                    if (l.indexOf(j) == 0) {
                        return l.substring(j.length, l.length)
                    }
                }
                return false
            },
            escape: function(j) {
                var div = document.createElement("div");
                div.appendChild(document.createTextNode(j));
                return div.innerHTML
            }
        };
        h = {
            addOptions: function(o, n) {
                var j, l, m, k = [];
                if (!d.getCookie("default_metric") || !d.getCookie("default_dimension") || !d.getCookie("default_swmetric")) {
                    j = "sessions";
                    l = "30daysAgo";
                    if (gadwpItemData.scope == "front-item" || gadwpItemData.scope == "admin-item") {
                        c = "pageviews"
                    } else {
                        c = "sessions"
                    }
                    d.setCookie("default_metric", j);
                    d.setCookie("default_dimension", l);
                    d.setCookie("default_swmetric", c)
                } else {
                    j = d.getCookie("default_metric");
                    l = d.getCookie("default_dimension");
                    m = d.getCookie("default_view");
                    c = d.getCookie("default_swmetric")
                }
                if (n == false) {
                    if (gadwpItemData.scope == "front-item" || gadwpItemData.scope == "admin-item") {
                        k = ""
                    } else {
                        k = '<span id="gadwp-swmetric-sessions" title="' + gadwpItemData.i18n[5] + '" class="dashicons dashicons-clock" style="font-size:22px;padding:4px;"></span>'
                    }
                    k += '<span id="gadwp-swmetric-users" title="' + gadwpItemData.i18n[6] + '" class="dashicons dashicons-admin-users" style="font-size:22px;padding:4px;"></span>';
                    k += '<span id="gadwp-swmetric-pageviews" title="' + gadwpItemData.i18n[7] + '" class="dashicons dashicons-admin-page" style="font-size:22px;padding:4px;"></span>';
                    jQuery(o).html(k);
                    jQuery("#gadwp-swmetric-" + c).css("color", "#008ec2")
                } else {
                    jQuery.each(n, function(p, q) {
                        if (p == j || p == l || p == m) {
                            k.push('<option value="' + p + '" selected="selected">' + q + "</option>")
                        } else {
                            k.push('<option value="' + p + '">' + q + "</option>")
                        }
                    });
                    jQuery(o).html(k.join(""))
                }
            },
            init: function() {
                var j;
                if (!jQuery("#gadwp-window" + b).length) {
                    return
                }
                if (jQuery("#gadwp-window" + b).html().length) {
                    return
                }
                j = '<div id="gadwp-container' + b + '">';
                if (gadwpItemData.viewList != false) {
                    j += '<select id="gadwp-sel-view' + b + '"></select>'
                }
                j += '<select id="gadwp-sel-period' + b + '"></select> ';
                j += '<select id="gadwp-sel-report' + b + '"></select>';
                j += '<div id="gadwp-sel-metric' + b + '" style="float:right;display:none;">';
                j += "</div>";
                j += '<div id="gadwp-progressbar' + b + '"></div>';
                j += '<div id="gadwp-status' + b + '"></div>';
                j += '<div id="gadwp-reports' + b + '"></div>';
                j += '<div style="text-align:right;width:100%;font-size:0.8em;clear:both;margin-right:5px;margin-top:10px;">';
                j += gadwpItemData.i18n[14];
                j += ' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_report&utm_medium=link&utm_content=back_report&utm_campaign=gadwp" rel="nofollow" style="text-decoration:none;font-size:1em;">GADWP</a>&nbsp;';
                j += "</div>";
                j += "</div>", jQuery("#gadwp-window" + b).append(j);
                h.addOptions("#gadwp-sel-view" + b, gadwpItemData.viewList);
                h.addOptions("#gadwp-sel-period" + b, gadwpItemData.dateList);
                h.addOptions("#gadwp-sel-report" + b, gadwpItemData.reportList);
                h.addOptions("#gadwp-sel-metric" + b, false)
            }
        };
        e = {
            oldViewPort: 0,
            orgChartTableChartData: "",
            tableChartData: "",
            orgChartPieChartsData: "",
            geoChartTableChartData: "",
            areaChartBottomStatsData: "",
            realtime: "",
            rtRuns: null,
            i18n: null,
            getTitle: function(j) {
                if (j == "admin-item") {
                    return jQuery("#gadwp" + b).attr("title")
                } else {
                    return document.getElementsByTagName("title")[0].innerHTML
                }
            },
            alertMessage: function(j) {
                jQuery("#gadwp-status" + b).css({
                    "margin-top": "3px",
                    "padding-left": "5px",
                    height: "auto",
                    color: "#000",
                    "border-left": "5px solid red"
                });
                jQuery("#gadwp-status" + b).html(j)
            },
            areaChartBottomStats: function(j) {
                e.areaChartBottomStatsData = j;
                if (jQuery.isArray(j)) {
                    if (!jQuery.isNumeric(j[0])) {
                        if (jQuery.isArray(j[0])) {
                            jQuery("#gadwp-reports" + b).show();
                            if (a.query == "visitBounceRate,bottomstats") {
                                e.drawAreaChart(j[0], true)
                            } else {
                                e.drawAreaChart(j[0], false)
                            }
                        } else {
                            e.throwDebug(j[0])
                        }
                    } else {
                        jQuery("#gadwp-reports" + b).show();
                        e.throwError("#gadwp-areachart" + b, j[0], "125px")
                    }
                    if (!jQuery.isNumeric(j[1])) {
                        if (jQuery.isArray(j[1])) {
                            jQuery("#gadwp-reports" + b).show();
                            e.drawBottomStats(j[1])
                        } else {
                            e.throwDebug(j[1])
                        }
                    } else {
                        jQuery("#gadwp-reports" + b).show();
                        e.throwError("#gadwp-bottomstats" + b, j[1], "40px")
                    }
                } else {
                    e.throwDebug(j)
                }
                NProgress.done()
            },
            orgChartPieCharts: function(j) {
                var k = 0;
                e.orgChartPieChartsData = j;
                if (jQuery.isArray(j)) {
                    if (!jQuery.isNumeric(j[0])) {
                        if (jQuery.isArray(j[0])) {
                            jQuery("#gadwp-reports" + b).show();
                            e.drawOrgChart(j[0])
                        } else {
                            e.throwDebug(j[0])
                        }
                    } else {
                        jQuery("#gadwp-reports" + b).show();
                        e.throwError("#gadwp-orgchart" + b, j[0], "125px")
                    }
                    for (k = 1; k < j.length; k++) {
                        if (!jQuery.isNumeric(j[k])) {
                            if (jQuery.isArray(j[k])) {
                                jQuery("#gadwp-reports" + b).show();
                                e.drawPieChart("piechart-" + k, j[k], e.i18n[k])
                            } else {
                                e.throwDebug(j[k])
                            }
                        } else {
                            jQuery("#gadwp-reports" + b).show();
                            e.throwError("#gadwp-piechart-" + k + b, j[k], "80px")
                        }
                    }
                } else {
                    e.throwDebug(j)
                }
                NProgress.done()
            },
            geoChartTableChart: function(j) {
                e.geoChartTableChartData = j;
                if (jQuery.isArray(j)) {
                    if (!jQuery.isNumeric(j[0])) {
                        if (jQuery.isArray(j[0])) {
                            jQuery("#gadwp-reports" + b).show();
                            e.drawGeoChart(j[0]);
                            e.drawTableChart(j[0])
                        } else {
                            e.throwDebug(j[0])
                        }
                    } else {
                        jQuery("#gadwp-reports" + b).show();
                        e.throwError("#gadwp-geochart" + b, j[0], "125px");
                        e.throwError("#gadwp-tablechart" + b, j[0], "125px")
                    }
                } else {
                    e.throwDebug(j)
                }
                NProgress.done()
            },
            orgChartTableChart: function(j) {
                e.orgChartTableChartData = j;
                if (jQuery.isArray(j)) {
                    if (!jQuery.isNumeric(j[0])) {
                        if (jQuery.isArray(j[0])) {
                            jQuery("#gadwp-reports" + b).show();
                            e.drawOrgChart(j[0])
                        } else {
                            e.throwDebug(j[0])
                        }
                    } else {
                        jQuery("#gadwp-reports" + b).show();
                        e.throwError("#gadwp-orgchart" + b, j[0], "125px")
                    }
                    if (!jQuery.isNumeric(j[1])) {
                        if (jQuery.isArray(j[1])) {
                            e.drawTableChart(j[1])
                        } else {
                            e.throwDebug(j[1])
                        }
                    } else {
                        e.throwError("#gadwp-tablechart" + b, j[1], "125px")
                    }
                } else {
                    e.throwDebug(j)
                }
                NProgress.done()
            },
            tableChart: function(j) {
                e.tableChartData = j;
                if (jQuery.isArray(j)) {
                    if (!jQuery.isNumeric(j[0])) {
                        if (jQuery.isArray(j[0])) {
                            jQuery("#gadwp-reports" + b).show();
                            e.drawTableChart(j[0])
                        } else {
                            e.throwDebug(j[0])
                        }
                    } else {
                        jQuery("#gadwp-reports" + b).show();
                        e.throwError("#gadwp-tablechart" + b, j[0], "125px")
                    }
                } else {
                    e.throwDebug(j)
                }
                NProgress.done()
            },
            drawTableChart: function(m) {
                var l, j, k;
                l = google.visualization.arrayToDataTable(m);
                j = {
                    page: "enable",
                    pageSize: 10,
                    width: "100%",
                    allowHtml: true
                };
                k = new google.visualization.Table(document.getElementById("gadwp-tablechart" + b));
                k.draw(l, j)
            },
            drawOrgChart: function(m) {
                var l, j, k;
                l = google.visualization.arrayToDataTable(m);
                j = {
                    allowCollapse: true,
                    allowHtml: true,
                    height: "100%"
                };
                k = new google.visualization.OrgChart(document.getElementById("gadwp-orgchart" + b));
                k.draw(l, j)
            },
            drawPieChart: function(o, m, n) {
                var l, j, k;
                l = google.visualization.arrayToDataTable(m);
                j = {
                    is3D: false,
                    tooltipText: "percentage",
                    legend: "none",
                    chartArea: {
                        width: "99%",
                        height: "80%"
                    },
                    title: n,
                    pieSliceText: "value",
                    colors: gadwpItemData.colorVariations
                };
                k = new google.visualization.PieChart(document.getElementById("gadwp-" + o + b));
                k.draw(l, j)
            },
            drawGeoChart: function(m) {
                var l, j, k;
                l = google.visualization.arrayToDataTable(m);
                j = {
                    chartArea: {
                        width: "99%",
                        height: "90%"
                    },
                    colors: [gadwpItemData.colorVariations[5], gadwpItemData.colorVariations[4]]
                };
                if (gadwpItemData.region) {
                    j.region = gadwpItemData.region;
                    j.displayMode = "markers";
                    j.datalessRegionColor = "EFEFEF"
                }
                k = new google.visualization.GeoChart(document.getElementById("gadwp-geochart" + b));
                k.draw(l, j)
            },
            drawAreaChart: function(n, o) {
                var m, j, l, k;
                m = google.visualization.arrayToDataTable(n);
                if (o) {
                    k = new google.visualization.NumberFormat({
                        suffix: "%",
                        fractionDigits: 2
                    });
                    k.format(m, 1)
                }
                j = {
                    legend: {
                        position: "none"
                    },
                    pointSize: 3,
                    colors: [gadwpItemData.colorVariations[0], gadwpItemData.colorVariations[4]],
                    chartArea: {
                        width: "99%",
                        height: "90%"
                    },
                    vAxis: {
                        textPosition: "in",
                        minValue: 0
                    },
                    hAxis: {
                        textPosition: "none"
                    }
                };
                l = new google.visualization.AreaChart(document.getElementById("gadwp-areachart" + b));
                l.draw(m, j)
            },
            drawBottomStats: function(j) {
                jQuery("#gdsessions" + b).html(j[0]);
                jQuery("#gdusers" + b).html(j[1]);
                jQuery("#gdpageviews" + b).html(j[2]);
                jQuery("#gdbouncerate" + b).html(j[3]);
                jQuery("#gdorganicsearch" + b).html(j[4]);
                jQuery("#gdpagespervisit" + b).html(j[5]);
                jQuery("#gdpagetime" + b).html(j[6]);
                jQuery("#gdpageload" + b).html(j[7]);
                jQuery("#gdsessionduration" + b).html(j[8])
            },
            rtOnlyUniqueValues: function(l, k, j) {
                return j.indexOf(l) === k
            },
            rtCountSessions: function(k, m) {
                var l = 0,
                    j = 0;
                for (j = 0; j < k.rows.length; j++) {
                    if (jQuery.inArray(m, k.rows[j]) > -1) {
                        l += parseInt(k.rows[j][6])
                    }
                }
                return l
            },
            rtGenerateTooltip: function(k) {
                var m = 0,
                    l = "",
                    j = 0;
                for (j = 0; j < k.length; j++) {
                    m += parseInt(k[j].count);
                    l += "<tr><td class='gadwp-pgdetailsl'>" + k[j].value + "</td><td class='gadwp-pgdetailsr'>" + k[j].count + "</td></tr>"
                }
                if (m) {
                    return ("<table>" + l + "</table>")
                } else {
                    return ("")
                }
            },
            rtPageDetails: function(y, n) {
                var w, o, r, x = 0,
                    v = 0,
                    m = 0,
                    s = 0,
                    t = 0,
                    z = 0,
                    C = 0,
                    q = 0,
                    l = 0,
                    u = "",
                    A = "",
                    p = "",
                    k = "",
                    B = "";
                y = y.rows;
                for (x = 0; x < y.length; x++) {
                    if (y[x][0] == n) {
                        o = y[x][5];
                        switch (y[x][3]) {
                            case "REFERRAL":
                                t += parseInt(y[x][6]);
                                u += "<tr><td class='gadwp-pgdetailsl'>" + y[x][1] + "</td><td class='gadwp-pgdetailsr'>" + y[x][6] + "</td></tr>";
                                break;
                            case "ORGANIC":
                                z += parseInt(y[x][6]);
                                A += "<tr><td class='gadwp-pgdetailsl'>" + y[x][2] + "</td><td class='gadwp-pgdetailsr'>" + y[x][6] + "</td></tr>";
                                break;
                            case "SOCIAL":
                                q += parseInt(y[x][6]);
                                p += "<tr><td class='gadwp-pgdetailsl'>" + y[x][1] + "</td><td class='gadwp-pgdetailsr'>" + y[x][6] + "</td></tr>";
                                break;
                            case "CUSTOM":
                                l += parseInt(y[x][6]);
                                k += "<tr><td class='gadwp-pgdetailsl'>" + y[x][1] + "</td><td class='gadwp-pgdetailsr'>" + y[x][6] + "</td></tr>";
                                break;
                            case "DIRECT":
                                C += parseInt(y[x][6]);
                                break
                        }
                    }
                }
                if (t) {
                    u = "<table><tr><td>" + e.i18n[0] + "(" + t + ")</td></tr>" + u + "</table><br />"
                }
                if (z) {
                    A = "<table><tr><td>" + e.i18n[1] + "(" + z + ")</td></tr>" + A + "</table><br />"
                }
                if (q) {
                    p = "<table><tr><td>" + e.i18n[2] + "(" + q + ")</td></tr>" + p + "</table><br />"
                }
                if (l) {
                    k = "<table><tr><td>" + e.i18n[3] + "(" + l + ")</td></tr>" + k + "</table><br />"
                }
                if (C) {
                    B = "<table><tr><td>" + e.i18n[4] + "(" + C + ")</td></tr></table><br />"
                }
                return ("<p><center><strong>" + o + "</strong></center></p>" + u + A + p + k + B)
            },
            rtRefresh: function() {
                if (e.render.focusFlag) {
                    a.from = false;
                    a.to = false;
                    a.query = "realtime";
                    jQuery.post(gadwpItemData.ajaxurl, a, function(j) {
                        if (jQuery.isArray(j)) {
                            jQuery("#gadwp-reports" + b).show();
                            e.realtime = j[0];
                            e.drawRealtime(e.realtime)
                        } else {
                            e.throwDebug(j)
                        }
                        NProgress.done()
                    })
                }
            },
            drawRealtime: function(y) {
                var j, n, o, w, t, r, x = 0,
                    v = [],
                    z = [],
                    p = [],
                    m = [],
                    q = [],
                    k = [],
                    l = [],
                    s = "",
                    o = [],
                    w = [],
                    t = [],
                    r = [],
                    u = ["REFERRAL", "ORGANIC", "SOCIAL", "CUSTOM"],
                    A = ["DIRECT", "NEW"];
                jQuery(function() {
                    jQuery("#gadwp-widget *").tooltip({
                        tooltipClass: "gadwp"
                    })
                });
                y = y[0];
                if (jQuery.isNumeric(y) || typeof y === "undefined") {
                    y = [];
                    y.totalsForAllResults = [];
                    y.totalsForAllResults["rt:activeUsers"] = "0";
                    y.rows = []
                }
                if (y.totalsForAllResults["rt:activeUsers"] !== document.getElementById("gadwp-online").innerHTML) {
                    jQuery("#gadwp-online").fadeOut("slow");
                    jQuery("#gadwp-online").fadeOut(500);
                    jQuery("#gadwp-online").fadeOut("slow", function() {
                        if ((parseInt(y.totalsForAllResults["rt:activeUsers"])) < (parseInt(document.getElementById("gadwp-online").innerHTML))) {
                            jQuery("#gadwp-online").css({
                                "background-color": "#FFE8E8"
                            })
                        } else {
                            jQuery("#gadwp-online").css({
                                "background-color": "#E0FFEC"
                            })
                        }
                        document.getElementById("gadwp-online").innerHTML = y.totalsForAllResults["rt:activeUsers"]
                    });
                    jQuery("#gadwp-online").fadeIn("slow");
                    jQuery("#gadwp-online").fadeIn(500);
                    jQuery("#gadwp-online").fadeIn("slow", function() {
                        jQuery("#gadwp-online").css({
                            "background-color": "#FFFFFF"
                        })
                    })
                }
                if (y.totalsForAllResults["rt:activeUsers"] == 0) {
                    y.rows = []
                }
                for (x = 0; x < y.rows.length; x++) {
                    v.push(y.rows[x][0]);
                    if (y.rows[x][3] == "REFERRAL") {
                        z.push(y.rows[x][1])
                    }
                    if (y.rows[x][3] == "ORGANIC") {
                        p.push(y.rows[x][2])
                    }
                    if (y.rows[x][3] == "SOCIAL") {
                        m.push(y.rows[x][1])
                    }
                    if (y.rows[x][3] == "CUSTOM") {
                        k.push(y.rows[x][1])
                    }
                    q.push(y.rows[x][3])
                }
                n = v.filter(e.rtOnlyUniqueValues);
                for (x = 0; x < n.length; x++) {
                    l[x] = {
                        pagepath: n[x],
                        count: e.rtCountSessions(y, n[x])
                    }
                }
                l.sort(function(C, B) {
                    return B.count - C.count
                });
                s = "";
                for (x = 0; x < l.length; x++) {
                    if (x < gadwpItemData.rtLimitPages) {
                        s += '<div class="gadwp-pline"><div class="gadwp-pleft"><a href="#" data-gadwp="' + e.rtPageDetails(y, l[x].pagepath) + '">' + l[x].pagepath.substring(0, 70) + '</a></div><div class="gadwp-pright">' + l[x].count + "</div></div>"
                    }
                }
                document.getElementById("gadwp-pages").innerHTML = '<br /><div class="gadwp-pg">' + s + "</div>";
                o = z.filter(e.rtOnlyUniqueValues);
                for (x = 0; x < o.length; x++) {
                    o[x] = {
                        value: o[x],
                        count: e.rtCountSessions(y, o[x])
                    }
                }
                o.sort(function(C, B) {
                    return B.count - C.count
                });
                w = p.filter(e.rtOnlyUniqueValues);
                for (x = 0; x < w.length; x++) {
                    w[x] = {
                        value: w[x],
                        count: e.rtCountSessions(y, w[x])
                    }
                }
                w.sort(function(C, B) {
                    return B.count - C.count
                });
                t = m.filter(e.rtOnlyUniqueValues);
                for (x = 0; x < t.length; x++) {
                    t[x] = {
                        value: t[x],
                        count: e.rtCountSessions(y, t[x])
                    }
                }
                t.sort(function(C, B) {
                    return B.count - C.count
                });
                r = k.filter(e.rtOnlyUniqueValues);
                for (x = 0; x < r.length; x++) {
                    r[x] = {
                        value: r[x],
                        count: e.rtCountSessions(y, r[x])
                    }
                }
                r.sort(function(C, B) {
                    return B.count - C.count
                });
                j = '<div class="gadwp-bigtext"><a href="#" data-gadwp="' + e.rtGenerateTooltip(o) + '"><div class="gadwp-bleft">' + e.i18n[0] + '</a></div><div class="gadwp-bright">' + e.rtCountSessions(y, u[0]) + "</div></div>";
                j += '<div class="gadwp-bigtext"><a href="#" data-gadwp="' + e.rtGenerateTooltip(w) + '"><div class="gadwp-bleft">' + e.i18n[1] + '</a></div><div class="gadwp-bright">' + e.rtCountSessions(y, u[1]) + "</div></div>";
                j += '<div class="gadwp-bigtext"><a href="#" data-gadwp="' + e.rtGenerateTooltip(t) + '"><div class="gadwp-bleft">' + e.i18n[2] + '</a></div><div class="gadwp-bright">' + e.rtCountSessions(y, u[2]) + "</div></div>";
                j += '<div class="gadwp-bigtext"><a href="#" data-gadwp="' + e.rtGenerateTooltip(r) + '"><div class="gadwp-bleft">' + e.i18n[3] + '</a></div><div class="gadwp-bright">' + e.rtCountSessions(y, u[3]) + "</div></div>";
                j += '<div class="gadwp-bigtext"><div class="gadwp-bleft">' + e.i18n[4] + '</div><div class="gadwp-bright">' + e.rtCountSessions(y, A[0]) + "</div></div>";
                j += '<div class="gadwp-bigtext"><div class="gadwp-bleft">' + e.i18n[5] + '</div><div class="gadwp-bright">' + e.rtCountSessions(y, A[1]) + "</div></div>";
                document.getElementById("gadwp-tdo-right").innerHTML = j
            },
            throwDebug: function(j) {
                jQuery("#gadwp-status" + b).css({
                    "margin-top": "3px",
                    "padding-left": "5px",
                    height: "auto",
                    color: "#000",
                    "border-left": "5px solid red"
                });
                if (j == "-24") {
                    jQuery("#gadwp-status" + b).html(gadwpItemData.i18n[15])
                } else {
                    jQuery("#gadwp-reports" + b).css({
                        "background-color": "#F7F7F7",
                        height: "auto",
                        "margin-top": "10px",
                        "padding-top": "50px",
                        "padding-bottom": "50px",
                        color: "#000",
                        "text-align": "center"
                    });
                    jQuery("#gadwp-reports" + b).html(j);
                    jQuery("#gadwp-reports" + b).show();
                    jQuery("#gadwp-status" + b).html(gadwpItemData.i18n[11]);
                    console.log("\n********************* GADWP Log ********************* \n\n" + j);
                    a = {
                        action: "gadwp_set_error",
                        response: j,
                        gadwp_security_set_error: gadwpItemData.security
                    };
                    jQuery.post(gadwpItemData.ajaxurl, a)
                }
            },
            throwError: function(l, j, k) {
                jQuery(l).css({
                    "background-color": "#F7F7F7",
                    height: "auto",
                    "padding-top": k,
                    "padding-bottom": k,
                    color: "#000",
                    "text-align": "center"
                });
                if (j == -21) {
                    jQuery(l).html(gadwpItemData.i18n[12])
                } else {
                    jQuery(l).html(gadwpItemData.i18n[13] + " (" + j + ")")
                }
            },
            render: function(j, o, n) {
                var k, q, p, l, m;
                if (o == "realtime") {
                    jQuery("#gadwp-sel-report" + b).hide()
                } else {
                    jQuery("#gadwp-sel-report" + b).show();
                    clearInterval(e.rtRuns)
                }
                jQuery("#gadwp-status" + b).html("");
                switch (o) {
                    case "today":
                        q = "today";
                        p = "today";
                        break;
                    case "yesterday":
                        q = "yesterday";
                        p = "yesterday";
                        break;
                    case "7daysAgo":
                        q = "7daysAgo";
                        p = "yesterday";
                        break;
                    case "14daysAgo":
                        q = "14daysAgo";
                        p = "yesterday";
                        break;
                    case "90daysAgo":
                        q = "90daysAgo";
                        p = "yesterday";
                        break;
                    case "365daysAgo":
                        q = "365daysAgo";
                        p = "yesterday";
                        break;
                    case "1095daysAgo":
                        q = "1095daysAgo";
                        p = "yesterday";
                        break;
                    default:
                        q = "30daysAgo";
                        p = "yesterday";
                        break
                }
                d.setCookie("default_metric", n);
                d.setCookie("default_dimension", o);
                if (typeof j !== "undefined") {
                    d.setCookie("default_view", j);
                    k = j
                } else {
                    k = false
                }
                if (gadwpItemData.scope == "admin-item") {
                    a = {
                        action: "gadwp_backend_item_reports",
                        gadwp_security_backend_item_reports: gadwpItemData.security,
                        from: q,
                        to: p,
                        filter: g
                    }
                } else {
                    if (gadwpItemData.scope == "front-item") {
                        a = {
                            action: "gadwp_frontend_item_reports",
                            gadwp_security_frontend_item_reports: gadwpItemData.security,
                            from: q,
                            to: p,
                            filter: gadwpItemData.filter
                        }
                    } else {
                        a = {
                            action: "gadwp_backend_item_reports",
                            gadwp_security_backend_item_reports: gadwpItemData.security,
                            projectId: k,
                            from: q,
                            to: p
                        }
                    }
                }
                if (o == "realtime") {
                    e.i18n = gadwpItemData.i18n.slice(20, 26);
                    e.render.focusFlag = 1;
                    jQuery(window).bind("focus", function(r) {
                        e.render.focusFlag = 1
                    }).bind("blur", function(r) {
                        e.render.focusFlag = 0
                    });
                    l = '<div id="gadwp-realtime' + b + '">';
                    l += '<div class="gadwp-rt-box">';
                    l += '<div class="gadwp-tdo-left">';
                    l += '<div class="gadwp-online" id="gadwp-online">0</div>';
                    l += "</div>";
                    l += '<div class="gadwp-tdo-right" id="gadwp-tdo-right">';
                    l += '<div class="gadwp-bigtext">';
                    l += '<div class="gadwp-bleft">' + e.i18n[0] + "</div>";
                    l += '<div class="gadwp-bright">0</div>';
                    l += "</div>";
                    l += '<div class="gadwp-bigtext">';
                    l += '<div class="gadwp-bleft">' + e.i18n[1] + "</div>";
                    l += '<div class="gadwp-bright">0</div>';
                    l += "</div>";
                    l += '<div class="gadwp-bigtext">';
                    l += '<div class="gadwp-bleft">' + e.i18n[2] + "</div>";
                    l += '<div class="gadwp-bright">0</div>';
                    l += "</div>";
                    l += '<div class="gadwp-bigtext">';
                    l += '<div class="gadwp-bleft">' + e.i18n[3] + "</div>";
                    l += '<div class="gadwp-bright">0</div>';
                    l += "</div>";
                    l += '<div class="gadwp-bigtext">';
                    l += '<div class="gadwp-bleft">' + e.i18n[4] + "</div>";
                    l += '<div class="gadwp-bright">0</div>';
                    l += "</div>";
                    l += '<div class="gadwp-bigtext">';
                    l += '<div class="gadwp-bleft">' + e.i18n[5] + "</div>";
                    l += '<div class="gadwp-bright">0</div>';
                    l += "</div>";
                    l += "</div>";
                    l += "</div>";
                    l += "<div>";
                    l += '<div id="gadwp-pages" class="gadwp-pages">&nbsp;</div>';
                    l += "</div>";
                    l += "</div>";
                    jQuery("#gadwp-reports" + b).html(l);
                    e.rtRefresh(e.render.focusFlag);
                    e.rtRuns = setInterval(e.rtRefresh, 55000)
                } else {
                    if (jQuery.inArray(n, ["referrers", "contentpages", "searches"]) > -1) {
                        l = '<div id="gadwp-orgcharttablechart' + b + '">';
                        l += '<div id="gadwp-orgchart' + b + '"></div>';
                        l += '<div id="gadwp-tablechart' + b + '"></div>';
                        l += "</div>";
                        jQuery("#gadwp-reports" + b).html(l);
                        jQuery("#gadwp-reports" + b).hide();
                        jQuery("#gadwp-sel-metric" + b).show();
                        a.query = "channelGrouping," + n;
                        a.metric = c;
                        jQuery.post(gadwpItemData.ajaxurl, a, function(r) {
                            e.orgChartTableChart(r)
                        })
                    } else {
                        if (n == "404errors") {
                            l = '<div id="gadwp-404tablechart' + b + '">';
                            l += '<div id="gadwp-tablechart' + b + '"></div>';
                            l += "</div>";
                            jQuery("#gadwp-reports" + b).html(l);
                            jQuery("#gadwp-reports" + b).hide();
                            jQuery("#gadwp-sel-metric" + b).show();
                            a.query = n;
                            a.metric = c;
                            jQuery.post(gadwpItemData.ajaxurl, a, function(r) {
                                e.tableChart(r)
                            })
                        } else {
                            if (n == "trafficdetails" || n == "technologydetails") {
                                l = '<div id="gadwp-orgchartpiecharts' + b + '">';
                                l += '<div id="gadwp-orgchart' + b + '"></div>';
                                l += '<div class="gadwp-floatwraper">';
                                l += '<div id="gadwp-piechart-1' + b + '" class="halfsize floatleft"></div>';
                                l += '<div id="gadwp-piechart-2' + b + '" class="halfsize floatright"></div>';
                                l += "</div>";
                                l += '<div class="gadwp-floatwraper">';
                                l += '<div id="gadwp-piechart-3' + b + '" class="halfsize floatleft"></div>';
                                l += '<div id="gadwp-piechart-4' + b + '" class="halfsize floatright"></div>';
                                l += "</div>";
                                l += "</div>";
                                jQuery("#gadwp-reports" + b).html(l);
                                jQuery("#gadwp-reports" + b).hide();
                                jQuery("#gadwp-sel-metric" + b).show();
                                if (n == "trafficdetails") {
                                    a.query = "channelGrouping,medium,visitorType,source,socialNetwork";
                                    e.i18n = gadwpItemData.i18n.slice(0, 5)
                                } else {
                                    e.i18n = gadwpItemData.i18n.slice(15, 20);
                                    a.query = "deviceCategory,browser,operatingSystem,screenResolution,mobileDeviceBranding"
                                }
                                a.metric = c;
                                jQuery.post(gadwpItemData.ajaxurl, a, function(r) {
                                    e.orgChartPieCharts(r)
                                })
                            } else {
                                if (n == "locations") {
                                    l = '<div id="gadwp-geocharttablechart' + b + '">';
                                    l += '<div id="gadwp-geochart' + b + '"></div>';
                                    l += '<div id="gadwp-tablechart' + b + '"></div>';
                                    l += "</div>";
                                    jQuery("#gadwp-reports" + b).html(l);
                                    jQuery("#gadwp-reports" + b).hide();
                                    jQuery("#gadwp-sel-metric" + b).show();
                                    a.query = n;
                                    a.metric = c;
                                    jQuery.post(gadwpItemData.ajaxurl, a, function(r) {
                                        e.geoChartTableChart(r)
                                    })
                                } else {
                                    l = '<div id="gadwp-areachartbottomstats' + b + '">';
                                    l += '<div id="gadwp-areachart' + b + '"></div>';
                                    l += '<div id="gadwp-bottomstats' + b + '">';
                                    l += '<div class="inside">';
                                    l += '<div class="small-box"><h3>' + gadwpItemData.i18n[5] + '</h3><p id="gdsessions' + b + '">&nbsp;</p></div>';
                                    l += '<div class="small-box"><h3>' + gadwpItemData.i18n[6] + '</h3><p id="gdusers' + b + '">&nbsp;</p></div>';
                                    l += '<div class="small-box"><h3>' + gadwpItemData.i18n[7] + '</h3><p id="gdpageviews' + b + '">&nbsp;</p></div>';
                                    l += '<div class="small-box"><h3>' + gadwpItemData.i18n[8] + '</h3><p id="gdbouncerate' + b + '">&nbsp;</p></div>';
                                    l += '<div class="small-box"><h3>' + gadwpItemData.i18n[9] + '</h3><p id="gdorganicsearch' + b + '">&nbsp;</p></div>';
                                    l += '<div class="small-box"><h3>' + gadwpItemData.i18n[10] + '</h3><p id="gdpagespervisit' + b + '">&nbsp;</p></div>';
                                    l += '<div class="small-box"><h3>' + gadwpItemData.i18n[26] + '</h3><p id="gdpagetime' + b + '">&nbsp;</p></div>';
                                    l += '<div class="small-box"><h3>' + gadwpItemData.i18n[27] + '</h3><p id="gdpageload' + b + '">&nbsp;</p></div>';
                                    l += '<div class="small-box"><h3>' + gadwpItemData.i18n[28] + '</h3><p id="gdsessionduration' + b + '">&nbsp;</p></div>';
                                    l += "</div>";
                                    l += "</div>";
                                    l += "</div>";
                                    jQuery("#gadwp-reports" + b).html(l);
                                    jQuery("#gadwp-reports" + b).hide();
                                    a.query = n + ",bottomstats";
                                    jQuery.post(gadwpItemData.ajaxurl, a, function(r) {
                                        e.areaChartBottomStats(r)
                                    })
                                }
                            }
                        }
                    }
                }
            },
            refresh: function() {
                if (jQuery("#gadwp-areachartbottomstats" + b).length > 0 && jQuery.isArray(e.areaChartBottomStatsData)) {
                    e.areaChartBottomStats(e.areaChartBottomStatsData)
                }
                if (jQuery("#gadwp-orgchartpiecharts" + b).length > 0 && jQuery.isArray(e.orgChartPieChartsData)) {
                    e.orgChartPieCharts(e.orgChartPieChartsData)
                }
                if (jQuery("#gadwp-geocharttablechart" + b).length > 0 && jQuery.isArray(e.geoChartTableChartData)) {
                    e.geoChartTableChart(e.geoChartTableChartData)
                }
                if (jQuery("#gadwp-orgcharttablechart" + b).length > 0 && jQuery.isArray(e.orgChartTableChartData)) {
                    e.orgChartTableChart(e.orgChartTableChartData)
                }
                if (jQuery("#gadwp-404tablechart" + b).length > 0 && jQuery.isArray(e.tableChartData)) {
                    e.tableChart(e.tableChartData)
                }
            },
            init: function() {
                if (!jQuery("#gadwp-reports" + b).length) {
                    return
                }
                if (jQuery("#gadwp-reports" + b).html().length) {
                    return
                }
                try {
                    NProgress.configure({
                        parent: "#gadwp-progressbar" + b,
                        showSpinner: false
                    });
                    NProgress.start()
                } catch (j) {
                    e.alertMessage(gadwpItemData.i18n[0])
                }
                e.render(jQuery("#gadwp-sel-view" + b).val(), jQuery("#gadwp-sel-period" + b).val(), jQuery("#gadwp-sel-report" + b).val());
                jQuery(window).resize(function() {
                    var k = jQuery(window).width() - e.oldViewPort;
                    if ((k < -5) || (k > 5)) {
                        e.oldViewPort = jQuery(window).width();
                        e.refresh()
                    }
                })
            }
        };
        h.init();
        e.init();
        jQuery("#gadwp-sel-view" + b).change(function() {
            jQuery("#gadwp-reports" + b).html("");
            e.init()
        });
        jQuery("#gadwp-sel-period" + b).change(function() {
            jQuery("#gadwp-sel-metric" + b).hide();
            jQuery("#gadwp-reports" + b).html("");
            e.init()
        });
        jQuery("#gadwp-sel-report" + b).change(function() {
            jQuery("#gadwp-sel-metric" + b).hide();
            jQuery("#gadwp-reports" + b).html("");
            e.init()
        });
        jQuery("[id^=gadwp-swmetric-]").click(function() {
            c = this.id.replace("gadwp-swmetric-", "");
            d.setCookie("default_swmetric", c);
            jQuery("#gadwp-swmetric-sessions").css("color", "#444");
            jQuery("#gadwp-swmetric-users").css("color", "#444");
            jQuery("#gadwp-swmetric-pageviews").css("color", "#444");
            jQuery("#" + this.id).css("color", "#008ec2");
            jQuery("#gadwp-reports" + b).html("");
            e.init()
        });
        if (gadwpItemData.scope == "admin-widgets") {
            return
        } else {
            return this.dialog({
                width: "auto",
                maxWidth: 510,
                height: "auto",
                modal: true,
                fluid: true,
                dialogClass: "gadwp wp-dialog",
                resizable: false,
                title: e.getTitle(gadwpItemData.scope),
                position: {
                    my: "top",
                    at: "top+100",
                    of: window
                }
            })
        }
    }
});

function GADWPReportLoad() {
    if (gadwpItemData.scope == "admin-widgets") {
        jQuery("#gadwp-window-1").gadwpItemReport(1)
    } else {
        jQuery(gadwpItemData.getSelector(gadwpItemData.scope)).click(function() {
            if (!jQuery("#gadwp-window-" + gadwpItemData.getID(this)).length > 0) {
                jQuery("body").append('<div id="gadwp-window-' + gadwpItemData.getID(this) + '"></div>')
            }
            jQuery("#gadwp-window-" + gadwpItemData.getID(this)).gadwpItemReport(gadwpItemData.getID(this))
        })
    }
    jQuery(window).resize(function() {
        gadwpItemData.responsiveDialog()
    });
    jQuery(document).on("dialogopen", ".ui-dialog", function(a, b) {
        gadwpItemData.responsiveDialog()
    })
};