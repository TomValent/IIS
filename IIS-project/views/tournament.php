<div class='right'>
    <a href='tournaments'><button>Back to tournaments</button></a>
</div>
<script>
    const ID = urlParams()['id'];
    function onLoad() {
        getContent("../index.php/frags/tournament_main?id="+ID, "#content", {}, () => {
            getAvailableTeams()
        })
    }
    $(() => {
        setupModal("#startModal")
        setupModal("#resultModal")
        setupModal("#detailModal")
        onLoad()
    })
    function getAvailableTeams() {
        let elem = $('#joinButtonContainer')[0]
        api.get({
            url: "../api.php/user/available_teams",
            data: {id: ID},
            success: (data) => {
                sel.html.innerHTML = ""
                data.teams.unshift({Name: 'Select team', ID: -1})
                for (const t of data.teams) {
                    let option = document.createElement("option")
                    option.value = t.ID
                    option.text = t.Name
                    sel.html.appendChild(option)
                }
                if (data.teams.length > 1) {
                    elem.style.visibility = 'visible'
                }
                else {
                    elem.style.visibility = 'hidden'
                }
            }
        })
    }
    let sel = {
        par: null,
        id: null,
        html: document.createElement("select")
    };
    function hideTeamSelect() {
        if (sel.par) {
            let b = sel.par.getElementsByTagName("button")[0]
            b.style.display = 'inline'
            sel.par = null
        }
        sel.html.selectedIndex = 0
        sel.html.style.display = 'none'
    }
    function showTeamSelect(elem) {
        if (sel.par === elem) {
            return
        }
        hideTeamSelect()
        let b = elem.getElementsByTagName("button")[0]
        b.style.display = 'none'
        sel.html.style.display = 'inline'
        elem.appendChild(sel.html)
        sel.par = elem
    }
    sel.html.addEventListener(
        'change',
        () => {
            if (sel.html.selectedIndex == 0) {
                return
            }
            let team_id = sel.html.value
            console.log(team_id)
            api.post({
                url: "../api.php/tournament/join",
                data: {
                    id: ID,
                    team_id: team_id
                },
                success: (data) => {
                    hideTeamSelect()
                    onLoad()
                },
                error: () => {
                    hideTeamSelect()
                }
            })
        },
        false
    );
    function joinTournament(elem) {
        if (elem)  {
            // team join
            showTeamSelect(elem)
        }
        else {
            // member join
            api.post({
                url: "../api.php/tournament/join",
                data: {id: ID},
                success: onLoad
            })
        }
    }

    function deleteTournament() {
        let data = {
            id: ID
        }
        api.post({
            url: <?php echo url("/api.php/tournament/delete") ?>,
            data: data,
        })
    }

    function leaveTournament(team_id) {
        let data = {
            id: ID
        }
        if (team_id) {
            data.team_id = team_id
        }
        api.post({
            url: "../api.php/tournament/leave",
            data: data,
            success: onLoad
        })
    }
    function showSelection(participants, round) {
        let count = Math.ceil(participants.length / 2)
        if (count <= 0) {
            console.log('not enough participants')
            return;
        }
        let pl = new Players
        for (const p of participants) {
            pl.addPlayer(p.name, p.id)
        }
        let pairs = []
        let div = $("#startContent")[0]
        div.innerHTML = ''
        for (let i = 0; i < count; i++) {
            let p = new Pair(pl)
            pairs.push(p)
            div.appendChild(p.html)
        }
        $('#confirmButton').click(() => {
            let elem = $("#startModalError")[0]
            elem.innerHTML = ''
            let data = [];
            for (const s of pairs) {
                data.push({
                    'time': s.date.value,
                    'p1': s.p1.selected.id,
                    'p2': s.p2.selected.id
                })
            }
            api.post({
                url: "../api.php/tournament/start_round",
                data: {'id': ID, 'round': round, 'pairs': data},
                success: onLoad,
                error: (message) => {
                    let elem = $("#startModalError")[0]
                    elem.innerHTML = message
                }
            })
        })
        $('#startTimeAllButton').click(() => {
            let time = $('#startTimeAll')[0].value;
            for (let p of pairs) {
                p.date.value = time
            }
        })
        let elem = $("#startModalError")[0]
        elem.innerHTML = ''
        openModal("#startModal");
    }
    function startTournament() {
        api.get({
            url: "../api.php/tournament/participants?id=" + ID,
            success: (data) => {
                showSelection(data.participants, 1)
            }
        })
    }
    function endTournament() {
        api.get({
            url: "../api.php/tournament/end?id=" + ID,
            success: onLoad
        })
    }
    function approveTournament() {
        api.post({
            url: "../api.php/user/approve_tournament",
            data: {t_id: ID},
            success: onLoad
        })
    }
    function updateParticipant(url, participant_id) {
        api.post({
            url: url,
            data: {t_id: ID, p_id: participant_id},
            success: onLoad
        })
    }
    function acceptParticipant(participant_id) {
        updateParticipant("../api.php/tournament/accept", participant_id);
    }
    function revokeParticipant(participant_id) {
        updateParticipant("../api.php/tournament/revoke", participant_id);
    }
    function kickParticipant(participant_id) {
        updateParticipant("../api.php/tournament/kick", participant_id);
    }
    function rejectParticipant(participant_id) {
        updateParticipant("../api.php/tournament/kick", participant_id);
    }
    const default_opt = {name: "Select player", id: -1}
    const bye_opt = {name: "BYE", id: -2}
    class PlayerSelect {
        constructor(players) {
            this.pl = players
            this.sel = document.createElement('select')
            this.sel.setAttribute('class', 'select-light')
            this.selected = {...default_opt}
            this.update(players)
            this.sel.addEventListener(
                'change',
                () => {
                    let prev = {...this.selected}
                    this.selected.name = this.sel.options[this.sel.selectedIndex].text
                    this.selected.id = this.sel.value
                    this.pl.take(this, this.selected, prev)
                },
                false
            );
        }
        update(players) {
            this.sel.innerHTML = ""
            this.addOption(default_opt)
            this.addOption(bye_opt)
            for (const p of players.values) {
                if (!p.taken || p.id == this.selected.id) {
                    this.addOption(p)
                }
            }
            this.sel.value = this.selected.id
        }
        addOption(player) {
            let option = document.createElement("option")
            option.value = player.id
            option.text = player.name
            this.sel.appendChild(option)
        }
    }
    class Players {
        constructor() {
            this.values = []
            this.selects = []
        }
        addPlayer(name, id) {
            this.values.push({name: name, id: id, taken: false})
        }
        set(player, state) {
            let p = this.values.find(o => o.id === Number(player.id))
            if (p) {
                p.taken = state
            }
        }
        createSelect() {
            let s = new PlayerSelect(this)
            this.selects.push(s)
            return s
        }
        take(sel, current, prev) {
            this.set(current, true)
            this.set(prev, false)
            for (let s of this.selects) {
                if (s !== sel) {
                    s.update(this)
                }
            }
        }
    }
    class Pair {
        constructor(players) {
            this.p1 = players.createSelect()
            this.p2 = players.createSelect()
            this.date = document.createElement("input")
            this.date.setAttribute("type", "datetime-local")
            let now = new Date()
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset() + 60)
            this.date.value = now.toISOString().slice(0,16)
            this.html = document.createElement("div")
            this.html.setAttribute('class', 'playerSelectBox')
            this.html.appendChild(this.date)
            this.html.appendChild(this.p1.sel)
            this.html.appendChild(this.p2.sel)
        }
    }
    function setResult(id) {
        api.get({
            url: "../api.php/match/get",
            data: {id: id, t_id: ID},
            success: (data) => {
                let bye = data.isBye
                $('#resultPointsA')[0].value = data.Points1
                $('#resultPointsB')[0].value = data.Points2
                $("#resultNameA")[0].innerHTML = data.Name[0]
                $("#resultNameB")[0].innerHTML = data.Name[1]
                let btn = $('#resultConfirmButton')[0]
                let checkA = $('#resultCheckA')[0]
                let checkB = $('#resultCheckB')[0]
                $('#resultPartB')[0].style.display = bye? 'none' : 'flex';
                checkA.checked = false
                checkB.checked = false
                if (data.Winner[0] || bye) {
                    checkA.checked = true
                }
                else if (data.Winner[1]) {
                    checkB.checked = true
                }
                checkA.onclick = () => {
                    if (checkA.checked) {
                        $('#resultBoxA').addClass('active')
                        $('#resultBoxB').removeClass('active')
                        checkB.checked = false
                    }
                    else {
                        $('#resultBoxA').removeClass('active')
                    }
                }
                checkB.onclick = () => {
                    if (checkB.checked) {
                        $('#resultBoxB').addClass('active')
                        $('#resultBoxA').removeClass('active')
                        checkA.checked = false
                    }
                    else {
                        $('#resultBoxB').removeClass('active')
                    }
                }
                btn.onclick = ()=> {
                    let elem = $("#resultModalError")[0]
                    elem.innerHTML = ''
                    let pointsA = $('#resultPointsA')[0].value
                    let pointsB = $('#resultPointsB')[0].value
                    let data = {
                        id: id,
                        t_id: ID,
                        points1: pointsA,
                        points2: pointsB
                    }
                    if (checkA.checked) {
                        data.winner = 0
                    }
                    else if (checkB.checked) {
                        data.winner = 1
                    }
                    api.post({
                        url: "../api.php/match/set_result",
                        data: data,
                        success: onLoad,
                        error: (message) => {
                            let elem = $("#resultModalError")[0]
                            elem.innerHTML = message
                        }
                    })
                }
                let elem = $("#resultModalError")[0]
                elem.innerHTML = ''
                openModal("#resultModal");
            }
        })
    }
    function onNextRound() {
        api.get({
            url: "../api.php/tournament/round_results?id=" + ID,
            success: (data) => {
                if (data.complete) {
                    showSelection(data.results, data.round + 1)
                }
            }
        })
    }
    function viewDetails() {
        $("#detail-content")[0].innerHTML = ''
        getContent("../index.php/frags/tournament_detail?id="+ID, "#detail-content")
        openModal("#detailModal")
    }
</script>

<div id="content"></div>
<div id="startModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Match creation</p>
        <div id="startContent">
        </div>
        <div class="confirmContainer">
            <button id="confirmButton">Confirm</button>
            <div class="startTimeAllBox">
                <input type="datetime-local" id="startTimeAll">
                <button id="startTimeAllButton">Set time for all</button>
            </div>
        </div>
        <span id="startModalError" class="error_msg"></span>
    </div>
</div>
<div id="resultModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p style="width: 10em">Match result</p>
        <div id="resultContent">
            <div class="flex-row" style="gap: 40px">
                <div class="flex-column">
                    <div class="resultWinnerBox" id="resultBoxA">
                        <label>
                            <span id="resultNameA"></span>
                            <input type="checkbox" class="resultCheckbox" name='test' id="resultCheckA">
                            <label for="resultCheckA"></label>
                        </label>
                    </div>
                    <div>
                        <input type="number" value="0" style="width: 6em" id="resultPointsA">
                        pts
                    </div>
                </div>
                <div class="flex-column" id="resultPartB">
                    <div class="resultWinnerBox" id="resultBoxB">
                        <label>
                            <input type="checkbox" class="resultCheckbox" name='test' id="resultCheckB">
                            <label for="resultCheckB"></label>
                            <span id="resultNameB"></span>
                        </label>
                    </div>
                    <div>
                        <input type="number" value="0" style="width: 6em" id="resultPointsB">
                        pts
                    </div>
                </div>
            </div>
        </div>
        <button id="resultConfirmButton">Confirm</button>
        <span id="resultModalError" class="error_msg"></span>
    </div>
</div>
<div id="detailModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="detail-content"></div>
    </div>
</div>
