document.addEventListener("DOMContentLoaded", function () {
  const journeyBlock = document.getElementById(
    "block-pettys-theme-our-company-journey-timeline"
  );
  if (!journeyBlock) return;

  const timelineItems = journeyBlock.querySelectorAll(
    ".paragraph--type--timeline-item"
  );
  if (!timelineItems || timelineItems.length === 0) return;

  let activeItemIndex = 0;
  const years = [];

  timelineItems.forEach((item, index) => {
    const year = item.querySelector("div:first-child").textContent.trim();
    years.push(year);

    if (index === 0) {
      item.classList.add("active");
      item.style.display = "flex";
    } else {
      item.style.display = "none";
    }
  });

  createTimelineNavigation();

  function createTimelineNavigation() {
    const navContainer = document.createElement("div");
    navContainer.className = "timeline-navigation";

    const prevBtn = document.createElement("button");
    prevBtn.className = "prev";
    prevBtn.innerHTML = '<i class="bi bi-chevron-left"></i>';
    prevBtn.disabled = activeItemIndex === 0;
    prevBtn.addEventListener("click", goToPrevSlide);

    const nextBtn = document.createElement("button");
    nextBtn.className = "next";
    nextBtn.innerHTML = '<i class="bi bi-chevron-right"></i>';
    nextBtn.disabled = activeItemIndex === timelineItems.length - 1;
    nextBtn.addEventListener("click", goToNextSlide);

    const yearsContainer = document.createElement("div");
    yearsContainer.className = "timeline-years";

    years.forEach((year, index) => {
      const yearBtn = document.createElement("span");
      yearBtn.className = "year";
      yearBtn.textContent = year;
      if (index === activeItemIndex) {
        yearBtn.classList.add("active");
      }
      yearBtn.addEventListener("click", () => goToSlide(index));
      yearsContainer.appendChild(yearBtn);
    });

    navContainer.appendChild(prevBtn);
    navContainer.appendChild(yearsContainer);
    navContainer.appendChild(nextBtn);

    journeyBlock.appendChild(navContainer);
  }

  function updateActiveItem() {
    const currentScrollY = window.scrollY || window.pageYOffset;

    document.body.style.overflow = "hidden";
    document.documentElement.style.scrollBehavior = "auto";

    timelineItems.forEach((item, index) => {
      if (index === activeItemIndex) {
        item.classList.add("active");
        item.style.display = "flex";

        if (typeof gsap !== "undefined") {
          gsap.fromTo(
            item,
            { opacity: 0, y: 30 },
            {
              opacity: 1,
              y: 0,
              duration: 0.7,
              ease: "power2.out",
              onUpdate: function () {
                window.scrollTo(0, currentScrollY);
              },
              onComplete: function () {
                document.body.style.overflow = "";
                document.documentElement.style.scrollBehavior = "";
                window.scrollTo(0, currentScrollY);
              },
            }
          );
        } else {
          document.body.style.overflow = "";
          document.documentElement.style.scrollBehavior = "";
          window.scrollTo(0, currentScrollY);
        }
      } else {
        item.classList.remove("active");
        item.style.display = "none";
      }
    });

    const prevBtn = journeyBlock.querySelector(".timeline-navigation .prev");
    const nextBtn = journeyBlock.querySelector(".timeline-navigation .next");
    const yearBtns = journeyBlock.querySelectorAll(
      ".timeline-navigation .year"
    );

    if (prevBtn) prevBtn.disabled = activeItemIndex === 0;
    if (nextBtn)
      nextBtn.disabled = activeItemIndex === timelineItems.length - 1;

    yearBtns.forEach((btn, index) => {
      if (index === activeItemIndex) {
        btn.classList.add("active");
      } else {
        btn.classList.remove("active");
      }
    });
  }

  function goToPrevSlide() {
    if (activeItemIndex > 0) {
      activeItemIndex--;
      updateActiveItem();
    }
  }

  function goToNextSlide() {
    if (activeItemIndex < timelineItems.length - 1) {
      activeItemIndex++;
      updateActiveItem();
    }
  }

  function goToSlide(index) {
    if (index >= 0 && index < timelineItems.length) {
      activeItemIndex = index;
      updateActiveItem();
    }
  }
});
