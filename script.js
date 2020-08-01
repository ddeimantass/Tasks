const url = "http://127.0.0.1:8000/api/task";
$(document).ready(function () {
    getList();

    function getList() {
        $.getJSON(url, function (data) {
            let table = "";
            $.each(data, function (index, row) {
                let cell = "<p>" + row.name + " (" + row.done_points + "/" + row.total_points + ")</p>";
                cell += getTasks(row.tasks);
                table += "<div class='border padding'>" + cell + "</div>";
            });
            $("#list .table").html(table);
        });
    }

    function getTasks(tasks) {
        if (!tasks.length)
            return "";

        let list = "<ul>";
        $.each(tasks, function (index, task) {
            list += "<li>" + (task.is_done ? '(V) ' : '(X) ') + task.title + " (" + task.points + ")</li>";
            list += getTasks(task.children);
        });

        list += "</ul>"

        return list;
    }
});