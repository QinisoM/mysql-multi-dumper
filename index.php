<html>
    <head>
        <meta charset="utf-8">
        <title>MySQL MultiDump(er)</title>
        <link href="/assets/css/bootstrap.min.css" rel="stylesheet" />
        <link href="/assets/font-awesome/css/font-awesome.min.css"rel="stylesheet" />
        <link href="/assets/css/style.css"rel="stylesheet" />
		<link rel="shortcut icon" type="image/x-icon" href="http://www.qinisomdletshe.co.za/icon.png">
    </head>
    <body>
        <form method="post" target="FormProcessor" action="process.php" id="theForm">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <h1 style="margin-bottom:15px">MySQL Multi-Dump(er)</h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-md-offset-3">
                        <div class="form-horizontal">
                            <fieldset>
                                <legend>Source Database</legend>
                                <div class="input-group" title="Enter Source DB Host" data-toggle="tooltip">
                                    <span class="input-group-addon"><i class="fa fa-desktop fa-fw fa-lg"></i></span>
                                    <input type="text" class="form-control" id="source-host" placeholder="IP Address" required="required" name="DbHost" />
                                </div>
                                <div class="input-group" title="Enter Source DB Username" data-toggle="tooltip">
                                    <span class="input-group-addon"><i class="fa fa-user fa-fw fa-lg"></i></span>
                                    <input type="text" class="form-control" id="source-user" placeholder="Username" required="required" name="HostUser" />
                                </div>
                                <div class="input-group" title="Enter Source DB Password" data-toggle="tooltip">
                                    <span class="input-group-addon"><i class="fa fa-key fa-fw fa-lg"></i></span>
                                    <input type="text" class="form-control" id="source-pass" placeholder="Password" name="HostPass" />
                                </div>
                                <div class="input-group" title="Enter Source DB Delay in seconds. (Default 0)" data-toggle="tooltip">
                                    <span class="input-group-addon"><i class="fa fa-clock-o fa-fw fa-lg"></i></span>
                                    <input type="text" class="form-control" id="source-delay" placeholder="Delay in seconds. (Default 0)" required="required" value="0" name="HostDelay" />
                                </div>
                            <fieldset>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-horizontal">
                            <fieldset>
                                <legend>Destination Database</legend>
                                <div class="input-group" title="Enter destination DB Host" data-toggle="tooltip">
                                    <span class="input-group-addon"><i class="fa fa-desktop fa-fw fa-lg"></i></span>
                                    <input type="text" class="form-control" id="dest-host" placeholder="IP Address" required="required" name="DbGuest" />
                                </div>
                                <div class="input-group" title="Enter destination DB Username" data-toggle="tooltip">
                                    <span class="input-group-addon"><i class="fa fa-user fa-fw fa-lg"></i></span>
                                    <input type="text" class="form-control" id="dest-user" placeholder="Username" required="required" name="GuestUser" />
                                </div>
                                <div class="input-group" title="Enter destination DB Password" data-toggle="tooltip">
                                    <span class="input-group-addon"><i class="fa fa-key fa-fw fa-lg"></i></span>
                                    <input type="text" class="form-control" id="dest-pass" placeholder="Password" name="GuestPass" />
                                </div>
                                <div class="input-group" title="Enter destination DB Delay in seconds. (Default 0)" data-toggle="tooltip">
                                    <span class="input-group-addon"><i class="fa fa-clock-o fa-fw fa-lg"></i></span>
                                    <input type="text" class="form-control" id="dest-delay" placeholder="Delay in seconds. (Default 0)" value="0" required="required" name="GuestDelay" />
                                </div>
                            <fieldset>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="form-horizontal" title="Select databas(e)..." data-toggle="tooltip">
                            <select id="source-db" style="width:100%" class="form-control" name="DbName[]" size="3" multiple="multiple" required="required"></select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="form-horizontal">
                            <fieldset style="margin-top:10px">
                                <legend style="margin-bottom:5px">Options</legend>
                                <div class="input-group" title="Enter Output Folder. (Writable)" data-toggle="tooltip">
                                    <span class="input-group-addon"><i class="fa fa-folder-open-o fa-fw fa-lg"></i></span>
                                    <input type="text" class="form-control" id="opt-" placeholder="/path/to/files/" required="required" name="OutputFolder" />
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-md-offset-3">
                        <div class="form-horizontal">
                            <div class="input-group" title="Maximum Rows per Table" data-toggle="tooltip">
                                <span class="input-group-addon"><i class="fa fa-arrow-up fa-fw fa-lg"></i></span>
                                <input type="text" class="form-control" id="row-limit" placeholder="Row Limit" required="required" name="RowLimit" value="1000" />
                            </div>
                            <div class="input-group" title="Check this to add 'LOCK TABLE'" data-toggle="tooltip">
                                <span class="input-group-addon"><i class="fa fa-square-o fa-fw fa-lg" id="chk-lock-table"></i>
                                    <input type="checkbox" value="1" id="opt-lock-table" name="LockTable" style="display:none" />
                                </span>
                                <input type="text" class="form-control" placeholder="Lock Tables" disabled="disabled" style="cursor:default" />
                            </div>
                            <div class="input-group" title="Make Windows Batch Files" data-toggle="tooltip">
                                <span class="input-group-addon"><i class="fa fa-dot-circle-o fa-fw fa-lg" id="radio-file-type-batch"></i>
                                    <input type="radio" value="sh" id="opt-file-type-batch" style="display:none" checked="checked" />
                                </span>
                                <input type="text" class="form-control" placeholder="Windows Batch Files" readonly="readonly" style="cursor:default" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-horizontal">
                            <div class="input-group" title="Number of rows for each File" data-toggle="tooltip">
                                <span class="input-group-addon"><i class="fa fa-expand fa-fw fa-lg"></i></span>
                                <input type="text" class="form-control" id="row-split" placeholder="Row Split" required="required" name="RowSplit" value="1000" />
                            </div>
                            <div class="input-group" title="Check this to output .zip files" data-toggle="tooltip">
                                <span class="input-group-addon"><i class="fa fa-square-o fa-fw fa-lg" id="chk-compress-files"></i>
                                    <input type="checkbox" value="1" id="opt-compress-files" name="Gzip" style="display:none" />
                                </span>
                                <input type="text" class="form-control" placeholder="Compressed Files (GZip)" disabled="disabled" style="cursor:default;backgrounf-color:default" />
                            </div>
                            <div class="input-group" title="Make Linux Bash Files" data-toggle="tooltip">
                                <span class="input-group-addon"><i class="fa fa-circle-o fa-fw fa-lg" id="radio-file-type-bash"></i>
                                    <input type="radio" value="sh" id="opt-file-type-bash" name="FileType" style="display:none" />
                                </span>
                                <input type="text" class="form-control" placeholder="Make Linux Bash Files" readonly="readonly" disabled="disabled" style="cursor:default" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3" id="err-msg"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <hr />
                        <input type="submit" value="Run" class="btn btn-primary btn-lg"  />
                    </div>
                </div>
            </div>
        </form>
        <iframe style="display:none" name="FormProcessor"></iframe>
        <div class="modal" id="progressModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">MySQL Multi-Dump... processing</h4>
                    </div>
                    <div class="modal-body">
                        <div id="ProcessResults" style="height:120px;margin-bottom:5px"></div>
                        <div style="text-align:center" id="Spinner">
                            <img src="/assets/octopus.gif">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <script src="/assets/js/jQuery.js"></script>
        <script src="/assets/js/bootstrap.min.js"></script>
        <script>var isFormValid=true;
            $(function () {
                $('[data-toggle="tooltip"]').tooltip({animation:true,delay:{'show':350,'hide':100}});

                $('#theForm').submit(function(){
                    if (!isFormValid) {
                        return false;
                    }
                    $('#progressModal').modal({'backdrop':'static'});
                });

                $('#progressModal').on('hide.bs.modal', function(){
                    $('#ProcessResults').html('');
                    $('#Spinner').css({'visibility':'visible'});
                });

                $('#radio-file-type-batch').click(function(){
                    $(this).removeClass('fa-circle-o').addClass('fa-dot-circle-o');
                    $('#radio-file-type-bash').removeClass('fa-dot-circle-o').addClass('fa-circle-o');
                    $('#opt-file-type-batch').prop('checked', 1);
                });

                $('#radio-file-type-bash').click(function(){
                    $(this).removeClass('fa-circle-o').addClass('fa-dot-circle-o');
                    $('#radio-file-type-batch').removeClass('fa-dot-circle-o').addClass('fa-circle-o');
                    $('#opt-file-type-bash').prop('checked', 1);
                });

                $('#chk-lock-table').click(function(){
                    if ($('#opt-lock-table').prop('checked')==true) {
                        $('#opt-lock-table').prop('checked', 0);
                        $(this).removeClass('fa-check-square-o').addClass('fa-square-o');
                    } else {
                        $('#opt-lock-table').prop('checked', 1);
                        $(this).removeClass('fa-square-o').addClass('fa-check-square-o');
                    }
                });

                $('#chk-compress-files').click(function(){
                    if ($('#opt-compress-files').prop('checked')==true) {
                        $('#opt-compress-files').prop('checked', 0);
                        $(this).removeClass('fa-check-square-o').addClass('fa-square-o');
                    } else {
                        $('#opt-compress-files').prop('checked', 1);
                        $(this).removeClass('fa-square-o').addClass('fa-check-square-o');
                    }
                });

                $('#source-host,#source-user,#source-pass').blur(function(){
                    var DbHost=$('#source-host').val(), HostUser=$('#source-user').val(), HostPass=$('#source-pass').val();
                    if (DbHost!='' && HostUser!='') {
                        $.post('/getDatabaseList.php', {DbHost:DbHost, HostUser:HostUser, HostPass:HostPass}, function(data){
                            theList = $('#source-db')[0];
                            theList.options.length=0;
                            $.each(data, function(i, v){
                                theList.options[theList.options.length] = new Option(v, v);
                            });
                        });
                    }
                });

                $('#row-split,#row-limit').blur(function(){
                    $('#err-msg').html('');
                    isFormValid = true;
                    if (
                        $('#row-split').val()!='' &&
                        $('#row-limit').val()!='' &&
                        $('#row-split').val()>$('#row-limit').val()
                    ) {
                        $('#err-msg').html('<div class="alert alert-danger" role="alert" style="margin-bottom:0">Invalid Row option combination, Row Split must be less than Row Limit</div>');
                        isFormValid=false;
                    }
                });
            });
        </script>
    </body>
</html>
