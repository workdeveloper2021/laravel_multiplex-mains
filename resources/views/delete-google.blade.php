<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete My Data | Multiplex Play</title>

    {{-- Bootstrap CSS (CDN) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    {{-- jQuery (Latest) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Google Sign-In --}}
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }
        aside.profile-card {
            max-width: 500px;
            margin: 60px auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            padding: 40px 30px;
            text-align: center;
        }
        #avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
            object-fit: cover;
        }
        h6#email {
            color: #555;
            margin-bottom: 20px;
        }
        .btn {
            min-width: 180px;
            font-weight: 500;
        }
        .btn-danger {
            background-color: #e3342f;
            border-color: #e3342f;
        }
        .btn-danger:hover {
            background-color: #c8231c;
            border-color: #bd2130;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0; top: 0;
            width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 8% auto;
            padding: 30px;
            border-radius: 14px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 26px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .g_id_signin {
            margin: 15px auto;
        }
        h3 {
            margin-top: 30px;
            font-size: 22px;
            color: #222;
        }
        #delete-title {
            font-size: 16px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <script>
        window.onSignIn = (response) => {
            const sData = response.credential.split(".");
            const gData = JSON.parse(atob(sData[1]));

            document.getElementById("avatar").src = gData.picture;
            document.getElementById("userName").innerText = gData.given_name + " " + gData.family_name;
            document.getElementById("email").innerText = gData.email;
            document.querySelector('.g_id_signin').style.display = 'none';
            document.getElementById('deleteData').style.display = 'block';
            document.getElementById('delete-title').innerHTML = 'Do you really want to delete your data?';
        };

        function deleteData() {
            document.getElementById("myModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        function closeDelete() {
            window.location.href = "https://multiplexplay.com/";
        }

        function deleteConfirmed() {
            const email = document.getElementById("email").innerText;

            $.ajax({
                url: "{{ route('delete-data') }}",
                type: 'POST', // ✅ Use POST here
                data: {
                    email: email,
                    _token: "{{ csrf_token() }}"
                },
                success: function (res) {
                    closeModal();
                    document.getElementById("deleteModal").style.display = "block";
                    document.getElementById("False").innerText = 'DataDeleted';//res.message;
                },
                error: function (xhr) {
                    closeModal();
                    const res = xhr.responseJSON;
                    document.getElementById("deleteModal").style.display = "block";
                    document.getElementById("False").innerText = 'DataDeleted';
                }
            });
        }
    </script>

    <aside class="profile-card">
        <header>
            <a href="#"><img src="https://multiplexplay.com/storage/banners/1752765686_logo1.png" id="avatar" class="hoverZoomLink"></a>
            <p style="font-size: 24px; color: #ff7b00; margin: 0;" id="userName"></p>
            <h6 id="email"></h6>
        </header>

        <div class="profile-bio">
            <p id="delete-title">Log in with your Gmail account</p>
        </div>

        <div class="g_id_signin"
            data-client_id="877177434602-50fgn80f4ee0lhtubbtfdoadq4df3kne.apps.googleusercontent.com"
            data-type="standard"
            data-size="large"
            data-theme="outline"
            data-callback="onSignIn">
        </div>

        <div id="g_id_onload"
            data-client_id="877177434602-50fgn80f4ee0lhtubbtfdoadq4df3kne.apps.googleusercontent.com"
            data-callback="onSignIn">
        </div>

        <button class="btn btn-danger mt-4" id="deleteData" style="display: none;" onclick="deleteData()">Delete My Data</button>

        <h3>Multiplex Play</h3>

        <!-- Confirmation Modal -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <p>Are you sure you want to delete your data?</p>
                <button class="btn btn-danger mt-3" onclick="deleteConfirmed()">Yes, Delete</button>
                <button class="btn btn-secondary mt-2" onclick="closeModal()">Cancel</button>
            </div>
        </div>

        <!-- Result Modal -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeDelete()">&times;</span>
                <p id="False">User successfully deleted!</p>
                <button class="btn btn-primary mt-3" onclick="closeDelete()">Close</button>
            </div>
        </div>
    </aside>
</body>
</html>
