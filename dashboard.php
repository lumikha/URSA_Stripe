<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<link rel="stylesheet" href="../style.css">


    <form id="dashboard_form">
        <div class="row">
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="Username">
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="Password">
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                 <select class="form-control">
                     <option value="" disabled selected>Recovery Question</option>
                     <option>What's the name of your first pet?</option>
                     <option>Who's your favorite Celebrity?</option>
                 </select>
            </div>
            <div class="col-md-3">
                 <input type="text" class="form-control" placeholder="Recovery Answer">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <select class="form-control">
                     <option value="" disabled selected>Notification Preference</option>
                     <option>Preference 1</option>
                     <option>Preference 2</option>
                 </select>
            </div>
            <div class="col-md-6">
                <label>Turn On Two-Step Access?</label>
                <label class="radio-inline"><input type="radio" name="two_step" value="yes" checked="checked">Yes</label>
                <label class="radio-inline"><input type="radio" name="two_step" value="no">No</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="well">
                    <strong>Reports:</strong>
                    <div class="well-lg">
                        Bright Local Data View
                    </div>
                </div>
            </div>
        </div>
    </form>

