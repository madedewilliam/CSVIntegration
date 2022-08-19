/*
* JS For Operations - Handling Issues Operations
* */
$(document).ready(function(){

    //Issues Table
    $('.modal-body').empty();
    $('.modal-body').append('<div align="center"><img src="assets/img/loader.gif" height="50px" width="50px"/></div>');
    $('#infoModal').modal('show');
    $.post("api/issuesHandler.php", {type: 'getIssues'}, function(table_data) {
        let data_array = table_data.split("|");
        let last_row_id = data_array[0];
        let the_table = data_array[1];
        $('#last_row_count').val(last_row_id);
        $('#issuesBody').append(the_table);
        let issuesTable = document.querySelector('#issuesTable');
        let dataTable = new simpleDatatables.DataTable(issuesTable);
        $('#infoModal').modal('hide');
    });

    //Get Issue Creation Form
    $(document).on("click", "#create_issue", function () {
        $('.modal-title').empty();
        $('.modal-title').append('Creating New Issue...');
        $.post("api/issuesHandler.php", {type: "getForm"}, function (data) {
            $('.modal-body').empty();
            $('.modal-body').append(data);
            $('#infoModal').modal('show');
        });
    });

    //Create New Issue
    $(document).on("click", ".submit_issue", function () {
        let count_id = $('#last_row_count').val();
        let title = $('#issue_title').val();
        let description = $('#issue_description').val();
        let client_name = $('#client_name').val();
        let priority = $('#issue_priority').val();
        let issue_type = $('#issue_type').val();
        if(title != '' && description != '' && client_name != '' && priority != '' && issue_type != ''){
            let values = {count_id:count_id,title:title,description:description,client_name:client_name,priority:priority, issue_type:issue_type,
                         type: "createIssue"};
            $('.modal-body').empty();
            $('.modal-body').append('<div align="center"><img src="assets/img/loader.gif" height="50px" width="50px"/></div>');
            $.post("api/issuesHandler.php", values, function (data) {
                $('.modal-title').empty();
                $('.modal-body').empty();
                the_result = data.split("|");
                result_value = the_result[0];
                result_set = the_result[1];
                if(result_value == 1){
                    $('.modal-title').append('Error: Creating New Issue.');
                    $('.modal-body').append(result_set);
                }else{
                    $('#issuesBody').empty();
                    $('.modal-title').append('Success');
                    $('.modal-body').append("New issue has been created.");
                    let issuesTable = document.querySelector('#issuesTable');
                    let dataTable = new simpleDatatables.DataTable(issuesTable);
                    $('#issuesBody').append(result_set);
                };
            });
        }else{
            $('.modal-title').empty();
            $('.modal-title').append('Error: Required Fields.');
            $('.modal-body').empty();
            $('.modal-body').append("Please enter all fields.");
            $('#infoModal').modal('show');
        }
        return false;
    });
});