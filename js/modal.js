const modals = document.querySelectorAll(".modal");

// TODO: use the `.is_visible` class

const openNewProjectModal = () => {
  const modal = document.querySelector("#new-project-modal");
  modal.style.visibility = "visible";
  modal.style.opacity = "1";
};

const openAddMemberModal = () => {
  const modal = document.querySelector("#add-member-modal");
  modal.style.visibility = "visible";
  modal.style.opacity = "1";
};

const openLogoutModal = () => {
  const modal = document.querySelector("#logout-modal");
  modal.style.visibility = "visible";
  modal.style.opacity = "1";
};

const openUploadDocumentModal = () => {
  const modal = document.querySelector("#upload-document-modal");
  modal.style.visibility = "visible";
  modal.style.opacity = "1";
};

document.addEventListener("click", (ev) => {
  modals.forEach((modal) => {
    if (modal.style.visibility === "visible") {
      const form = modal.querySelector("form");
      const openBtn = document.querySelector(`#${modal.id}-btn`);
      if (
        ev.target !== form &&
        !form.contains(ev.target) &&
        ev.target !== openBtn
      ) {
        modal.style.visibility = "hidden";
        modal.style.opacity = "0";
      }
    }
  });
});
