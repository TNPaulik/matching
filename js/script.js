// extending jQuery by function to get url params.. strange that this was needed
$.urlParam = function(name){
    let results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    return results === null ? null : results[1] || 0;
}

// the matcher object to be extended
let matcher = {};

// group name of the matches you are making
matcher.foldername = "karat";

// value of .match field
matcher.match = "";

// the matches array of already matched fields
matcher.matches = [];

// content of matches.json
matcher.matchesStr = null;

// name of matches you currently matching gotten from the url
matcher.matchesName = $.urlParam("matches");

// the fields splitted by .split(/({.+?})/)
matcher.subfields = {};

// temp var for texts
matcher.text = "";

// our selected field with table, ie table.field
matcher.currentOur = "";

// splits the fields into subfields by {.*} - to get the magic parts
matcher.getSubFields = function() {
    const regexp = /({.+?})/g;
    for (let key in matcher.matches) {
        const array = [...matcher.matches[key].matchAll(regexp)];
        matcher.subfields[key] = [];
        for (let index in array) {
            matcher.subfields[key].push(array[index][0]);
        }
    }
}

// checks for string occurrence in an array
matcher.checkForSubstringInArray = function (a, s) {
    for (let i in a) {
        if (a[i].includes(s)) {
            return true;
        }
    }
    return false;
}

// highlights the fields you matched already and are then not empty
matcher.highlightEmpty = function() {
    matcher.getSubFields();

    $(".fieldOur").each(function () {
        let $this = $(this);
        let table = $this.parent().parent().attr("data-name");
        let field = $this.attr("data-name");
        let key = table + "." + field;
        if (matcher.matches[key] === undefined || matcher.matches[key] === "") {
            $this.addClass("empty");
            $this.removeClass("set");
        } else {
            $this.addClass("set");
            $this.removeClass("empty");
        }
    });

    $(".fieldTheir").each(function () {
        let $this = $(this);
        let table = $this.parent().parent().attr("data-name");
        let field = $this.attr("data-name");
        let key = "" + table + "." + field + "";
        for (let iSub in matcher.subfields) {
            if (matcher.checkForSubstringInArray(matcher.subfields[iSub], key)) {
                $this.addClass("set");
                $this.removeClass("empty");
                break;
            } else {
                $this.addClass("empty");
                $this.removeClass("set");
            }
        }
    });
}

$(document).ready(function() {

    // if not set yet, get if from the html node
    if (matcher.matchesName === null) {
        matcher.matchesName = $(".currentMatches > option").first().text();
    }

    // if not set yet, get if from the .json by ajax
    if (matcher.matchesStr === null) {
        $.get('matches/'+matcher.foldername+'/'+matcher.matchesName+'.json?time='+Date.now(), function(data) {
            matcher.matches = data.matches;
        }, 'json').fail(function (e) {
            $.get('createNew.php?name='+matcher.matchesName, function (data) {
                let url = window.location.href;
                url = url.split("?")[0];
                window.location = url + "?matches=" + matcher.matchesName
            });
        });
    }
    $(".result").text(JSON.stringify(matcher.matches));

    // hide empty fields and tables where there are less then 3 empty values
    $(".hideEmpty").click(function () {
        let $this = $(this);
        if ($this.val() == "hideEmpty") {
            $this.val("showEmpty")
        } else {
            $this.val("hideEmpty")
        }
        let $emptys = $('.their .fieldEmpty');
        $emptys.each(function () {
            $(this).toggleClass("hidden");
        });
        $emptys = $('.their .tableEmpty');
        $emptys.each(function () {
            $(this).toggleClass("hidden");
        });
    });

    // selecting a field of the db which is migrated into
    $(".fieldOur").click(function () {
        let $this = $(this);
        if ($this.hasClass("selected")) {
            $this.removeClass("selected");
            $(".currentOur").text("");
            $(".match").val("");
        } else {
            $(".fieldOur").removeClass("selected");
            $this.addClass("selected");
            matcher.text = $this.attr("data-name");
            matcher.currentOur = $this.parent().parent().attr("data-name") + "." + matcher.text;
            $(".currentOur").text(matcher.currentOur);
            $(".match").val(matcher.matches[matcher.currentOur]);
            $(".fieldTheir").removeClass("selected");
        }
    });

    // selecting a field of the db which is migrated from
    $(".fieldTheir").click(function () {
        let $this = $(this);
        $this.addClass("selected");
        matcher.text = $this.attr("data-name");
        matcher.match = $(".match").val();
        matcher.match += "{" + $this.parent().parent().attr("data-name") + "." + matcher.text + "}";
        $(".match").val(matcher.match);
        matcher.matches[matcher.currentOur] = matcher.match;
    });

    // if changed, save in array
    $(".match").change(function () {
        matcher.match = $(this).val();
        matcher.matches[matcher.currentOur] = matcher.match;
    });

    // create new relation for foreign db
    $(".createrel").click(function () {
        let $thisCr = $(this)
        // 1. step - select main table
        if ($thisCr.data('step') == null) {
            $thisCr.data('step', 'maintable')
            $thisCr.val('x Rel')
            $(".our").css('opacity', 0.2)
            $(".their .db").css('border', '1px solid aqua')
            $(".mainTable").val('pls select the main table')
            // 2. step - select the key of main table
            $(".table").click(function () {
                let $thisT = $(this)
                let name = $thisT.data('name')
                $thisCr.data('maintable', name)
                $(".mainTable").val('pls select the key of this table to relate')
                $(".fkPos").val('maintable:'+name)
                $(".table").off("click");
                $thisCr.data('step', 'primarykey')
                // 3. step - select the foreignkey (and table) of foreign table to relate
                $(".field").click(function () {
                    let $thisF = $(this)
                    let name = $thisF.data('name')
                    $thisCr.data('primarykey', name)
                    $(".mainTable").val('pls select the foreignkey of foreign table to relate')
                    $(".fkPos").val($(".fkPos").val()+', pk:'+name)
                    $(".field").off("click");
                    $thisCr.data('step', 'foreignkey')
                    // 4. step - click here to save relation
                    $(".field").click(function () {
                        let $thisF = $(this)
                        let name = $thisF.data('name')
                        $thisCr.data('foreignkey', name)
                        let table = $thisF.parent().parent().attr("data-name");
                        $thisCr.data('foreigntable', table)
                        $(".mainTable").val('done')
                        $(".fkPos").val($(".fkPos").val()+', ft: ' + table + ', fk:'+name)
                        $(".field").off("click");
                        $thisCr.data('step', 'done')
                        $thisCr.val('click here to save relation')
                        return false
                    })
                    return false
                })
                return false
            })
        } else { // selecting done, save it
            if ($thisCr.data('step') == 'done') {
                let params = {
                    'maintable': $thisCr.data('maintable'),
                    'primarykey': $thisCr.data('primarykey'),
                    'foreigntable': $thisCr.data('foreigntable'),
                    'foreignkey': $thisCr.data('foreignkey'),
                }
                $.post("addrel.php", params, function(data) {
                    if (data != "") {
                        $(".rels").val(data.trim());
                    } else {
                        $(".rels").val("fählah");
                    }
                });
            }
            $thisCr.data('step', null)
            $thisCr.val('+ Rel')
            $(".our").css('opacity', 1)
            $(".their .db").css('border', '')
        }
        return false;
    });

    // create a new table matching ie: our sale to their angebote
    $(".createtablematching").click(function () {
        let $thisTm = $(this)
        if ($thisTm.data('step') == null) {
            $thisTm.data('step', 'ourtable')
            $thisTm.val('x Tablematching')
            $(".their .db").css('opacity', 0.2)
            $(".our").css('border', '1px solid aqua')
            $(".mainTable").val('pls select our table')
            // 1. step - select our table
            $(".our .table").click(function () {
                let $thisOur = $(this)
                let nameOur = $thisOur.data('name')
                $(".mainTable").val(nameOur + ' our table selected')
                $(".their .db").css('opacity', 1)
                $(".our").css('border', '')
                $(".our").css('opacity', 0.2)
                $(".their .db").css('border', '1px solid aqua')
                $(".our .table").off("click");
                //2. step - select there table and save result via ajax
                $(".their .table").click(function () {
                    let $thisTheir = $(this)
                    let nameTheir = $thisTheir.data('name')
                    $(".mainTable").val('Table matching complete. ' + nameOur + ' <-> ' + nameTheir + '')
                    let params = {
                        'our': nameOur,
                        'their': nameTheir,
                    }
                    $.post("addTm.php", params, function (data) {
                        if (data != "") {
                            $(".tablematchings").val(data.trim());
                        } else {
                            $(".tablematchings").val("fählah");
                        }
                    });
                    $thisTm.data('step', null)
                    $thisTm.val('+ Tablematching')
                    $(".our").css('opacity', 1)
                    $(".their .db").css('border', '')
                    return false
                })
                return false
            })
        } else {
            $thisTm.data('step', null)
            $thisTm.val('+ Tablematching')
            $(".our").css('opacity', 1)
            $(".our").css('border', '')
            $(".their .db").css('opacity', 1)
            $(".their .db").css('border', '')
        }
        return false;
    });

    // manages the size of the textarea outputs to the right side
    $("textarea").focus(function () {
        let $this = $(this)
        $("textarea").css("height", "5%")
        $this.css("height", "69%")
    });

    // saves current matches variable to the json file
    $(".tojson").click(function () {
        let matchesJson = JSON.stringify(matcher.matches);
        createCookie('matches', matchesJson);
        $.post("saveDescs.php", {matches: matchesJson, their: $('.currentMatches').val()}, function(data) {
            $(".result").text(data);
        });
        return false;
    });

    // for selecting different matches subfiles, ie. sale, order, product,...
    $('.currentMatches').change(function () {
        let url = window.location.href;
        url = url.split("?")[0];
        window.location = url + "?matches=" + $('.currentMatches').val()
    });

    // gets a random of the current selection
    $(".getRandomValueQuery").click(function () {
        let postData = {
            query: $('.match').val()
        };
        $.post("getRandomValueQuery.php", postData, function(data) {
            if (data != "[]") {
                data = JSON.parse(data);
                $(".fkPos").val(data[0][0]);
            } else {
                $(".fkPos").val("");
            }
        });
        return false;
    });

    // calls the request to generate the current sqls // don't forget to save first
    $(".getsql").click(function () {
        $.get("migration.php?name="+$('.currentMatches').val(), function(data) {
            if (data != "") {
                $(".resultSql").val(data.trim());
            } else {
                $(".resultSql").val("fählah");
            }
        });
        return false;
    });

    // gets a random value of the current field, to look what's inside it
    $(".getRandomValue").click(function () {
        let $this = $(this);
        let postData = {
            tablename: $this.attr("data-tablename"),
            fieldname: $this.attr("data-name"),
            their: $('.currentMatches').val()
        };
        $.post("getRandomValue.php", postData, function(data) {
            if (data != "[]") {
                data = JSON.parse(data);
                $(".fkPos").val(data[0][0]);
            } else {
                $(".fkPos").val("");
            }
            return false;
        });
        return false;
    });

    // toggles fields if you click table name
    $(".tableName").click(function () {
        $(this).next().toggle();
    });

    // collapses all table field divs. ie. after search
    $(".zuklappen").click(function () {
        let name = $(this).attr("data-name");
        $(".group[data-name="+name+"]").hide();
    });

    // click to see not matched fields
    $(".highlight").click(function () {
        highlightEmpty();
    });

    // search the db below by field or table names
    $(".search").focus(function () {
        let text = $(this).val();
        if (text == "search here for database table and fieldnames below") {
            $(this).val('');
        }
    });
    $(".search").keyup(function () {

        let text = $(this).val();
        let regex = new RegExp(text, "i");
        let dbName = $(this).attr("data-name");

        $(".db[data-name='"+dbName+"'] .field").each(function () {
            $(this).hide();
        })
        $(".db[data-name='"+dbName+"'] .field").filter(function () {
            return regex.test($(this).text());
        }).each(function () {
            $(this).show();
        })
        $(".db[data-name='"+dbName+"'] .group").each(function () {
            $(this).parent().show();
        })
        $(".db[data-name='"+dbName+"'] .group").each(function () {
            let allHidden = true;
            $(this).find(".field").each(function () {
                if($(this).css('display') == 'block') {
                    allHidden = false;
                }
            });
            if (allHidden) {
                $(this).parent().hide();
            }
        })

        $(".db[data-name='"+dbName+"'] .tableName").filter(function () {
            return regex.test($(this).text());
        }).each(function () {
            $(this).parent().show();
        })

        $(".db[data-name='"+dbName+"'] .group").each(function () {
            $(this).show();
        })
    });

    // highlights empty at loading. the timeout is to make a new thread for it... lags a few seconds when it loops through everything
    setTimeout(function () {
        matcher.highlightEmpty();
    }, 300);
});