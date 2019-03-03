/**
 * Script to compare 2 versions. Handle semver
 *
 * @param v1 string
 * @param v2 string
 * @param options
 * @returns {number} -1 if v1 < v2, 0 if v1  == v2, 1 if v1 > v2
 *
 *@note versionCompare("2.1.2","2.2.0") -> -1
 * versionCompare("2.21.2","2.2.0") ->  1
 * versionCompare("2.1.2","2.2.0") ->  -1
 * versionCompare("2.1.2","2.1.2") ->  0
 * versionCompare("2.1.2","2.1.2-beta3") ->  1
 * versionCompare("2.1.2-beta2","2.1.2-beta3") ->  -1
 * versionCompare("2.1.2-beta2","2.1.2-beta3") ->  -1
 * versionCompare("2.1.2-alpha2","2.1.2-beta3") ->  -1
 * versionCompare("2.1.2-beta2","2.1.2-alpha3") ->  1
 */
var versionCompare = function(v1, v2, options) {
    var zeroExtend = options && options.zeroExtend,
        v1parts = v1.split('.'),
        v2parts = v2.split('.');

    if (zeroExtend) {
        while (v1parts.length < v2parts.length) v1parts.push("0");
        while (v2parts.length < v1parts.length) v2parts.push("0");
    }

    for (var i = 0; i < v1parts.length; ++i) {
        if (v2parts.length === i) {
            return 1;
        }
        var v1Int = parseInt(v1parts[i], 10);
        var v2Int = parseInt(v2parts[i], 10);
        if (v1Int === v2Int) {
            var v1Lex = v1parts[i].substr((""+v1Int).length);
            var v2Lex = v2parts[i].substr((""+v2Int).length);
            if (v1Lex === '' && v2Lex !== '') return 1;
            if (v1Lex !== '' && v2Lex === '') return -1;
            if (v1Lex !== '' && v2Lex !== '') return v1Lex > v2Lex ? 1 : -1;
        }
        else if (v1Int > v2Int) {
            return 1;
        }
        else {
            return -1;
        }
    }

    if (v1parts.length !== v2parts.length) {
        return -1;
    }

    return 0;
};

var displayVersionMessage = function() {
    var current_version = $("#version").text();
    $.get( "https://api.github.com/repos/n7consulting/Incipio/releases/latest", function( data ) {
        var latest_version = data.tag_name.substring(1); // remove v of tag "v2.1.2"
        var message = {label: "danger", text: "Une erreur s'est produite lors de la comparaison des versions"};;

        if(data.tag_name !== undefined) {
            var version_diff = versionCompare(current_version, latest_version);
            if (version_diff === -1) {
                message = {label: "info", text: "Une nouvelle version de Jeyser est disponible. " +
                "Pensez à faire la mise à jour. "+
                "<a href='"+data.html_url+"' target='_blank'>Lire plus sur "+data.tag_name+"</a>"};
            } else if (version_diff === 0) {
                message = {label: "success", text: "Votre version de Jeyser CRM est à jour"};
            }
            else if (version_diff === 1) {
                message = {label: "info", text: "Votre version semble être en avance sur les versions officielles de Jeyser."};
            }
        }
        $("#version-info").html('<div class="alert alert-'+message.label+'">'+message.text+'</div>')
    });
};
