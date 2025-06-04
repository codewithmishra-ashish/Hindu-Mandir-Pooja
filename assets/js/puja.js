document.addEventListener("DOMContentLoaded", () => {
    // FAQ toggle functionality
    document.querySelectorAll(".faq-question").forEach(button => {
        button.addEventListener("click", () => {
            // Close all answers first
            document.querySelectorAll(".faq-answer").forEach(answer => {
                answer.style.display = "none"; // Hide all answers
                if (answer.previousElementSibling) {
                    answer.previousElementSibling.classList.remove("active"); // Remove active class from questions
                }
            });
  
            // Toggle the clicked question
            const answer = button.nextElementSibling;
            if (answer.style.display === "block") {
                answer.style.display = "none"; // Hide if it's already open
            } else {
                answer.style.display = "block"; // Show if it's closed
            }
            button.classList.toggle("active"); // Toggle active class for the clicked question
        });
    });
  
    // Countdown functionality
    const countdown = () => {
        const endDate = new Date("October 31, 2024 00:00:00").getTime();
        const now = new Date().getTime();
        const gap = endDate - now;
  
        const second = 1000;
        const minute = second * 60;
        const hour = minute * 60;
        const day = hour * 24;
  
        const textDay = Math.floor(gap / day);
        const textHour = Math.floor((gap % day) / hour);
        const textMinute = Math.floor((gap % hour) / minute);
        const textSecond = Math.floor((gap % minute) / second);
  
        const daysElement = document.getElementById('days');
        const hoursElement = document.getElementById('hours');
        const minutesElement = document.getElementById('minutes');
        const secondsElement = document.getElementById('seconds');
  
        if (daysElement && hoursElement && minutesElement && secondsElement) {
            daysElement.innerText = textDay;
            hoursElement.innerText = textHour;
            minutesElement.innerText = textMinute;
            secondsElement.innerText = textSecond;
        }
    };
  
    // Ensure countdown only runs if necessary elements exist
    if (
        document.getElementById('days') &&
        document.getElementById('hours') &&
        document.getElementById('minutes') &&
        document.getElementById('seconds')
    ) {
        setInterval(countdown, 1000);
    }
  
    // Header Scroll Effect
    window.addEventListener('scroll', () => {
        const header = document.querySelector('header');
        if (header) {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
    });
  
    // Toggle Navbar Menu
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const navbarMenu = document.querySelector('.navbar-menu');
  
    if (hamburgerMenu && navbarMenu) {
        hamburgerMenu.addEventListener('click', () => {
            navbarMenu.classList.toggle('active');
        });
    }
  
    // Benefits Toggle Functionality
    document.querySelectorAll('.benefit-card').forEach(card => {
        const toggleArrow = card.querySelector('.toggle-arrow');
        if (toggleArrow) {
            toggleArrow.addEventListener('click', () => {
                card.classList.toggle('expanded');
            });
        }
    });
  
    // Scroll to Package Functionality
    function scrollToPackage(packageId) {
        const packageElement = document.getElementById(packageId);
        if (packageElement) {
            packageElement.scrollIntoView({ behavior: 'smooth' });
        }
    }
  });
  