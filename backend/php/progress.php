<?php
session_start();
include 'connect.php';

// Redirect if user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../../frontend/pages/book-appointment.html");
    exit();
}

$user_email = $_SESSION['email'];

// Fetch user name from the database
$stmt = $conn->prepare("SELECT name FROM user WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_name = $user['name'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracker</title>
    <link rel="stylesheet" href="../../frontend/assets/css/progress.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>! 🌿</h1>
    <p>Track your progress and make each day count!</p>

    <!-- Progress Form -->
    <form id="progressForm">
        <input type="hidden" name="user_email" value="<?php echo $user_email; ?>">

        <label>Mood:</label>
        <div class="mood-slider-container">
            <input type="range" name="mood" id="moodSlider" min="1" max="5" step="1" value="3">
            <span id="moodEmoji">😐</span>
        </div>

        <label>Gratitude:</label>
        <input type="text" name="gratitude_1" placeholder="1st gratitude">
        <input type="text" name="gratitude_2" placeholder="2nd gratitude">
        <input type="text" name="gratitude_3" placeholder="3rd gratitude">

        <label>Daily Affirmation:</label>
        <input type="text" name="affirmation" placeholder="Write your affirmation">

        <label>Reflection:</label>
        <textarea name="reflection" placeholder="Write your thoughts..."></textarea>

        <label for="tasks-conntainer">Daily Tasks:</label>
            <div class="tasks-container">
                <label><input type="checkbox" name="task1"> Meditate for 10 minutes</label>
                <label><input type="checkbox" name="task2"> Drink 8 glasses of water</label>
                <label><input type="checkbox" name="task3"> Read for 15 minutes</label>
                <label><input type="checkbox" name="task4"> Write in journal</label>
                <label><input type="checkbox" name="task5"> Exercise for 30 minutes</label>
                <label><input type="checkbox" name="task6"> Practice deep breathing</label>
            </div>
        <button type="submit" class="btn-save">Save Progress</button>
    </form>

    <p id="statusMessage"></p>

    <!-- 📅 Calendar for Viewing Past Progress -->
    <div class="calendar-container">
        <h2>View Past Progress</h2>
        <input type="text" id="progressCalendar" placeholder="Select a date">
    </div>

    <!-- Modal for Past Progress -->
    <div id="progressModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const progressForm = document.getElementById("progressForm");

    progressForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const formData = new FormData(progressForm);
        
        fetch("save_progress.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById("statusMessage").textContent = data;
        })
        .catch(error => console.error("Error:", error));
    });

    // 🎨 Mood Slider with Emoji Updates
    const moodSlider = document.getElementById("moodSlider");
    const moodEmoji = document.getElementById("moodEmoji");
    const moodEmojis = ["😢", "😐", "🙂", "😊", "😃"];

    moodSlider.addEventListener("input", function () {
        const moodIndex = moodSlider.value - 1;
        moodEmoji.textContent = moodEmojis[moodIndex];
    });

    // 📅 Calendar Feature for Past Progress
    const calendar = flatpickr("#progressCalendar", {
        enableTime: false,
        dateFormat: "Y-m-d",
        onChange: function(selectedDates, dateStr) {
            fetch("fetch_progress.php?date=" + dateStr)
            .then(response => response.json())
            .then(data => {
                const modal = document.getElementById("progressModal");
                const modalContent = document.getElementById("modalContent");
                if (data.length > 0) {
                    let progressHTML = `<h3>Progress on ${dateStr}</h3>`;
                    data.forEach(progress => {
                        const moodIndex = progress.mood - 1;
                        progressHTML += `
                            <p><strong>Mood:</strong> ${moodEmojis[moodIndex]}</p>
                            <p><strong>Gratitude:</strong> ${progress.gratitude_1}, ${progress.gratitude_2}, ${progress.gratitude_3}</p>
                            <p><strong>Affirmation:</strong> ${progress.affirmation}</p>
                            <p><strong>Reflection:</strong> ${progress.reflection}</p>
                        `;
                    });
                    modalContent.innerHTML = progressHTML;
                } else {
                    modalContent.innerHTML = `<p>No progress recorded on this date.</p>`;
                }
                modal.style.display = "block";
            });
        }
    });

    // Modal Close Functionality
    const modal = document.getElementById("progressModal");
    const closeModal = document.querySelector(".close");

    closeModal.onclick = function () {
        modal.style.display = "none";
    };

    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
});
</script>


<!-- Floating Dashboard Button -->
<a href="client-dashboard.php" class="dashboard-btn" title="Go to Dashboard">
    <i class="fas fa-home"></i>
</a>

<!-- Font Awesome (for icons) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

<!-- Floating Button CSS -->
<style>
    .dashboard-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #6a9fb5; /* Pastel Blue */
        color: white;
        font-size: 22px;
        padding: 12px 16px;
        border-radius: 50%;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: 0.3s ease;
    }

    .dashboard-btn:hover {
        background: #568a99; /* Slightly darker blue */
        transform: scale(1.1);
    }

    .dashboard-btn i {
        margin: 0;
    }
</style>






</body>
</html>
