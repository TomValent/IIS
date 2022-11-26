<script>
    let cnt = 0;
    function mockAddTeam() {
        cnt++;
        let n = "Team " + cnt;
        api.post({
            url: <?php echo url("/mock.php/add_team") ?>,
            data: {func: 'add_team', name: n},
            success: (data) => {
                if (typeof data.error !== "undefined") {
                    console.log(data.error)
                }
                else {
                    console.log('team added')
                }

            }
        })
    }
</script>
<button onclick="mockAddTeam()">addTeam</button>
