const listings = [
  {
    name: "BrightPixel Studio",
    category: "Technology",
    location: "Lagos",
    rating: 4.9,
    desc: "Web design, branding and digital marketing.",
    initial: "B",
    type: "tech",
    featured: true
  },

  {
    name: "TasteBudz Kitchen",
    category: "Food & Restaurants",
    location: "Port Harcourt",
    rating: 4.8,
    desc: "Catering, small chops and event food.",
    initial: "T",
    type: "food",
    featured: true
  },

  {
    name: "Glow Beauty Lounge",
    category: "Beauty & Wellness",
    location: "Abuja",
    rating: 4.7,
    desc: "Beauty treatments, makeup and styling.",
    initial: "G",
    type: "beauty",
    featured: false
  },

  {
    name: "FixRight Home Care",
    category: "Home Services",
    location: "Abuja",
    rating: 4.8,
    desc: "Reliable home repairs, cleaning and maintenance.",
    initial: "F",
    type: "home",
    featured: false
  },

  {
    name: "FrameStory Media",
    category: "Events & Media",
    location: "Ibadan",
    rating: 4.9,
    desc: "Event photography and creative video coverage.",
    initial: "F",
    type: "events",
    featured: true
  },

  {
    name: "NextStep Tutors",
    category: "Education",
    location: "Lagos",
    rating: 4.8,
    desc: "Private tutoring and exam preparation.",
    initial: "N",
    type: "education",
    featured: false
  }
];


const grid = document.getElementById("listingGrid");
const emptyState = document.getElementById("emptyState");

const searchInput = document.getElementById("searchInput");
const locationFilter = document.getElementById("locationFilter");

let currentSort = "featured";


function renderListings(items) {

  const sorted = [...items].sort((a, b) => {

    if (currentSort === "rating") {
      return b.rating - a.rating;
    }

    return Number(b.featured) - Number(a.featured);

  });


  grid.innerHTML = sorted.map(item => {

    return `
      <article class="listing-card">

        <div class="listing-visual ${item.type}">

          ${
            item.featured
              ? '<span class="feature-tag">FEATURED</span>'
              : ""
          }

          <span class="visual-letter">
            ${item.initial}
          </span>

        </div>


        <div class="listing-body">

          <div class="listing-top">

            <div>

              <h3>
                ${item.name}
              </h3>

              <div class="listing-category">
                ${item.category}
              </div>

            </div>

            <span class="verified">
              ✓ Verified
            </span>

          </div>


          <p class="muted">
            ${item.desc}
          </p>


          <div class="listing-row">

            <span class="rating">
              ★ ${item.rating.toFixed(1)}
            </span>

            <span>
              ${item.location}
            </span>

          </div>


          <button
            class="listing-btn"
            data-name="${item.name}"
          >
            View business
          </button>

        </div>

      </article>
    `;

  }).join("");


  emptyState.hidden = sorted.length !== 0;


  document
    .querySelectorAll(".listing-btn")
    .forEach(button => {

      button.addEventListener("click", () => {

        alert(
          `Business profile demo: ${button.dataset.name}\n\n` +
          `The full business profile, phone contact and WhatsApp ` +
          `system will be connected in the backend stage.`
        );

      });

    });

}


function applyFilters(scrollToResults = true) {

  const searchTerm =
    searchInput.value
      .trim()
      .toLowerCase();

  const location =
    locationFilter.value;


  const filtered =
    listings.filter(item => {

      const searchableText =
        `${item.name} ${item.category} ${item.location} ${item.desc}`
          .toLowerCase();


      const matchesSearch =
        !searchTerm ||
        searchableText.includes(searchTerm);


      const matchesLocation =
        !location ||
        item.location === location;


      return matchesSearch && matchesLocation;

    });


  renderListings(filtered);


  if (scrollToResults) {

    document
      .getElementById("discover")
      .scrollIntoView({
        behavior: "smooth",
        block: "start"
      });

  }

}


/* SEARCH BUTTON */

document
  .getElementById("searchBtn")
  .addEventListener(
    "click",
    () => applyFilters(true)
  );


/* ENTER KEY SEARCH */

searchInput.addEventListener(
  "keydown",
  event => {

    if (event.key === "Enter") {
      applyFilters(true);
    }

  }
);


/* LOCATION CHANGE */

locationFilter.addEventListener(
  "change",
  () => applyFilters(false)
);


/* POPULAR SEARCHES */

document
  .querySelectorAll("[data-search]")
  .forEach(button => {

    button.addEventListener(
      "click",
      () => {

        searchInput.value =
          button.dataset.search;

        locationFilter.value = "";

        applyFilters(true);

      }
    );

  });


/* CATEGORY BUTTONS */

document
  .querySelectorAll("[data-category]")
  .forEach(button => {

    button.addEventListener(
      "click",
      () => {

        searchInput.value =
          button.dataset.category;

        locationFilter.value = "";

        applyFilters(true);

      }
    );

  });


/* SORTING */

document
  .querySelectorAll(".filter-btn")
  .forEach(button => {

    button.addEventListener(
      "click",
      () => {

        document
          .querySelectorAll(".filter-btn")
          .forEach(btn => {
            btn.classList.remove("active");
          });


        button.classList.add("active");


        currentSort =
          button.dataset.sort;


        applyFilters(false);

      }
    );

  });


/* MOBILE MENU */

const menuBtn =
  document.getElementById("menuBtn");

const mobileMenu =
  document.getElementById("mobileMenu");


menuBtn.addEventListener(
  "click",
  () => {

    mobileMenu.classList.toggle("open");

  }
);


mobileMenu
  .querySelectorAll("a")
  .forEach(link => {

    link.addEventListener(
      "click",
      () => {
        mobileMenu.classList.remove("open");
      }
    );

  });


/* BUSINESS LISTING MODAL */

const modal =
  document.getElementById("listingModal");

const openListing =
  document.getElementById("openListing");

const closeListing =
  document.getElementById("closeListing");


openListing.addEventListener(
  "click",
  () => {

    modal.classList.add("open");

    modal.setAttribute(
      "aria-hidden",
      "false"
    );

  }
);


closeListing.addEventListener(
  "click",
  () => {

    modal.classList.remove("open");

    modal.setAttribute(
      "aria-hidden",
      "true"
    );

  }
);


modal.addEventListener(
  "click",
  event => {

    if (event.target === modal) {
      closeListing.click();
    }

  }
);


/* BUSINESS FORM */

document
  .getElementById("listingForm")
  .addEventListener(
    "submit",
    event => {

      event.preventDefault();


      const formData =
        Object.fromEntries(
          new FormData(event.target)
        );


      const saved =
        JSON.parse(
          localStorage.getItem(
            "naijahub_business_submissions"
          ) || "[]"
        );


      saved.push({

        ...formData,

        submittedAt:
          new Date().toISOString()

      });


      localStorage.setItem(
        "naijahub_business_submissions",
        JSON.stringify(saved)
      );


      document
        .getElementById("formMessage")
        .textContent =
        "✓ Your demo listing was saved in this browser.";


      event.target.reset();

    }
  );


/* INITIAL LOAD */

renderListings(listings);