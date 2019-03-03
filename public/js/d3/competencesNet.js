/**
 * (c) Cotrino, 2012 (http://www.cotrino.com/)
 *
 */

var w = 0, h = 0;
var chart = "network";
var networkChart = {
    vis: null,
    nodes: [],
    labelAnchors: [],
    labelAnchorLinks: [],
    links: [],
    force: null,
    force2: null
};

var chordChart = {
    links: [], // Square matrix
    data: []
};

function restart() {

    if (d3.select("#graph") !== null) {
        d3.select("#graph").remove();
    }
    var graphHolder = $('#graphHolder');
    w = graphHolder.width();
    h = graphHolder.height();

    // clear network, if available
    if (networkChart.force !== null) {
        networkChart.force.stop();
    }
    if (networkChart.force2 !== null) {
        networkChart.force2.stop();
    }
    networkChart.nodes = [];
    networkChart.labelAnchors = [];
    networkChart.labelAnchorLinks = [];
    networkChart.links = [];

    // clear chord, if available
    chordChart.links = [];
    chordChart.data = [];

    drawNetwork();

}

function skillColor(links, total_links) {
    if (links / total_links >= 0.20) {
        return "#ff162d"
    }
    if (links / total_links >= 0.10) {
        return "#ff7d15"
    }
    if (links / total_links >= 0.05) {
        return "#fff026"
    }
    return "#ffcda7"
}

function memberColor(links) {
    if (links >= 10) {
        return "#0a0e81"
    }
    if (links >= 7) {
        return "#5a60ec"
    }
    if (links >= 4) {
        return "#b7faff"
    }
    return "#65ff61"
}

function drawNetwork() {

    buildNetwork();

    networkChart.vis = d3.select("#graphHolder").append("svg:svg").attr("id", "graph").attr("width", w).attr("height", h);

    networkChart.force = d3.layout.force().size([w, h])
        .nodes(networkChart.nodes).links(networkChart.links)
        .gravity(1).linkDistance(100).charge(-3000)
        .linkStrength(function (x) {
            return x.weight * 10
        });
    networkChart.force.start();

    // brings everything towards the center of the screen
    networkChart.force2 = d3.layout.force()
        .nodes(networkChart.labelAnchors).links(networkChart.labelAnchorLinks)
        .gravity(0).linkDistance(0).linkStrength(8).charge(-100).size([w, h]);
    networkChart.force2.start();

    var link = networkChart.vis.selectAll("line.link")
        .data(networkChart.links).enter()
        .append("svg:line").attr("class", "link")
        .style("stroke", function (d) {
            return d.color
        });

    var node = networkChart.vis.selectAll("g.node")
        .data(networkChart.force.nodes())
        .enter()
        .append("svg:g").attr("id", function (d) {
            return d.label
        })
        .attr("class", "node");

    node.append("svg:circle")
        .attr("id", function (d) {
            return "c_" + d.label
        })
        .attr("r", function (d) {
            return d.size
        })
        .style("fill", function (d) {
            return d.type === "member" ? memberColor(d.links) : skillColor(d.links, total_liens);
        })
        .style("stroke", "#FFF")
        .style("stroke-width", 2);

    node.call(networkChart.force.drag);
    node.on("mouseover", function (d) {
        showInformation(d);
    });

    var anchorLink = networkChart.vis.selectAll("line.anchorLink")
        .data(networkChart.labelAnchorLinks);

    var anchorNode = networkChart.vis.selectAll("g.anchorNode")
        .data(networkChart.force2.nodes())
        .enter()
        .append("svg:g")
        .attr("class", "anchorNode");

    anchorNode.append("svg:circle")
        .attr("id", function (d) {
            return "ct_" + d.node.label
        })
        .attr("r", 0)
        .style("fill", "#FFF");

    anchorNode.append("svg:text")
        .attr("id", function (d) {
            return "t_" + d.node.label
        })
        .text(function (d, i) {
            return i % 2 === 0 ? "" : d.node.label
        })
        .style("fill", function (d) {
            return d.node.type === "skill" ? "#000000" : "#ba9d92"
        })
        .style("font-family", "Arial")
        .style("font-size", 10)
        .style("font-weight", function (d) {
            return d.node.type === "skill" ? "bold" : ""
        })
        .on("mouseover", function (d) {
            showInformation(d);
        });

    var updateLink = function () {
        this.attr("x1", function (d) {
            return d.source.x;
        })
            .attr("y1", function (d) {
                return d.source.y;
            })
            .attr("x2", function (d) {
                return d.target.x;
            })
            .attr("y2", function (d) {
                return d.target.y;
            });

    };

    var updateNode = function () {
        this.attr("transform", function (d) {
            return "translate(" + d.x + "," + d.y + ")";
        });

    };

    networkChart.force.on("tick", function () {
        networkChart.force2.start();
        node.call(updateNode);
        anchorNode.each(function (d, i) {
            if (i % 2 === 0) {
                d.x = d.node.x;
                d.y = d.node.y;
            } else {
                var b = this.childNodes[1].getBBox();
                var diffX = d.x - d.node.x;
                var diffY = d.y - d.node.y;
                var dist = Math.sqrt(diffX * diffX + diffY * diffY);
                var shiftX = b.width * (diffX - dist) / (dist * 2);
                shiftX = Math.max(-b.width, Math.min(0, shiftX));
                var shiftY = 5;
                this.childNodes[1].setAttribute("transform", "translate(" + shiftX + "," + shiftY + ")");
            }
        });
        anchorNode.call(updateNode);
        link.call(updateLink);
        anchorLink.call(updateLink);
    });

}

function buildNetwork() {

    var newMapping = [];
    var k = 0;

    //création du réseau de noeud
    for (var i = 0; i < nodesArray.length; i++) {
        var node = nodesArray[i];
        var draw = true;
        if (draw) {//on se garde la possibilité ultérieurement dans le dev de ne pas afficher certains noeuds
            newMapping[node.id] = i;
            networkChart.nodes.push(node);
            networkChart.labelAnchors.push({node: node});
            networkChart.labelAnchors.push({node: node});
            k++;
        } else {
            newMapping[i] = -1;
        }
    }
    for (var j = 0; j < linksArray.length; j++) {
        var link = linksArray[j];
        // or the nodes exist
        if (newMapping[link.source] != -1 && newMapping[link.target] != -1) {
            var newLink = {
                source: nodesArray[newMapping[link.source]],
                target: nodesArray[newMapping[link.target]],
                weight: link.weight,
                color: link.color
            };
            networkChart.links.push(newLink);
        }
    }

    // link labels to circles
    for (var l = 0; l < networkChart.nodes.length; l++) {
        networkChart.labelAnchorLinks.push({source: l * 2, target: l * 2 + 1, weight: 1});
    }
}

function skillDetails(node) {
    return 'Compétence ' + node.label + '. Intervenants: ' + node.links
}

function memberDetails(node) {
    return 'Membre ' + node.label + '. Compétences: ' + node.links
}

function showInformation(node) {
    if (node.hasOwnProperty('label')) { // some general node also exists in the chart. They should not be took into account
        $("#nodeInfos").html(node.type === 'member' ? memberDetails(node) : skillDetails(node));
    }
}
