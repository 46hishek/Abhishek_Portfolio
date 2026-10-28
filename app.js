
/* Contact form — XAMPP PHP backend */

const contactForm = document.querySelector("#contact-form");
const contactStatus = document.querySelector("#form-status");

if (contactForm && contactStatus) {
  contactForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const name = contactForm.elements.name.value.trim();
    const email = contactForm.elements.email.value.trim();
    const message = contactForm.elements.message.value.trim();
    const button = contactForm.querySelector('button[type="submit"]');

    if (
      !name ||
      name.length > 80 ||
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) ||
      email.length > 254 ||
      message.length < 10 ||
      message.length > 2000
    ) {
      contactStatus.textContent =
        "Please enter a valid name, email and message (10–2000 characters).";
      return;
    }

    if (button) button.disabled = true;
    contactStatus.textContent = "Sending message...";

    try {
      const response = await fetch("./contact.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify({ name, email, message })
      });

      const text = await response.text();
      let result;

      try {
        result = JSON.parse(text);
      } catch {
        throw new Error(
          "Server returned HTML instead of JSON. Check Apache and the contact.php path."
        );
      }

      if (!response.ok) {
        throw new Error(result.message || "Failed to send message.");
      }

      contactForm.reset();
      contactStatus.textContent =
        result.message || "Message submitted successfully.";
    } catch (error) {
      contactStatus.textContent =
        error.message || "Server connection failed. Please try again.";
    } finally {
      if (button) button.disabled = false;
    }
  });
}
