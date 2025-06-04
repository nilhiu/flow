const burgerBtn = document.querySelector("#burger-menu");
const sidebar = document.querySelector("aside");

burgerBtn.addEventListener("click", (e) => {
  sidebar.classList.toggle("sidebar-open");
});

document.addEventListener("click", (e) => {
  if (sidebar.classList.contains("sidebar-open")) {
    if (e.target !== sidebar && !burgerBtn.contains(e.target)) {
      sidebar.classList.remove("sidebar-open");
    }
  }
});
