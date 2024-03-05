const { render, useState, useEffect } = wp.element;

function App() {
  const [activeTab, setActiveTab] = useState("tab1");
  const [postTypes, setPostTypes] = useState([]);
  const [checkedPostTypes, setCheckedPostTypes] = useState({});
  const [updateSuccess, setUpdateSuccess] = useState(false);

  const tabs = [
    { name: "tab1", title: "General" },
    { name: "tab2", title: "Exclude full post types" },
    { name: "tab3", title: "Exclude individual posts" },
    { name: "tab4", title: "Exclude taxonomies" },
  ];

  useEffect(() => {
    fetch(`${obiOptions.root}obiRCPT/v1/post-types`)
      .then((response) => response.json())
      .then((data) => {
        const postTypes = [];
        const checkedPostTypes = {};

        for (const postType in data) {
          postTypes.push(postType);
          checkedPostTypes[postType] = data[postType]; // This was previously `data[postType].status`, but `data[postType]` is already the status itself
        }

        setPostTypes(postTypes);
        setCheckedPostTypes(checkedPostTypes);
      });
  }, []);

  const handleCheckChange = (postType) => {
    const newCheckedStatus = {
      ...checkedPostTypes,
      [postType]: !checkedPostTypes[postType],
    };
    setCheckedPostTypes(newCheckedStatus);
  };

  const handleUpdateClick = () => {
    fetch(`${obiOptions.root}obiRCPT/v1/update-post-type-status`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": obiOptions.nonce, // include the nonce in the request headers
      },
      body: JSON.stringify(checkedPostTypes),
    }).then((response) => {
      if (response.ok) {
        setUpdateSuccess(true);
        setTimeout(() => setUpdateSuccess(false), 3000); // hide the success message after 3 seconds
    } else {
        alert('An error occurred while updating the settings.');
    }
});
};

  return (
    <div>
      <header
        style={{
          backgroundColor: "#f0f0f1",
          width: "100%",
          padding: "10px 0 10px 0",
        }}
      >
        <h1 style={{ textAlign: "left", color: "#5e2129" }}>
          Obi Exclude from Search
        </h1>
        <sup>Version {obiOptions.version}</sup>
      </header>

      <div
        style={{
          display: "flex",
          justifyContent: "left",
          background: "#f0f0f1",
          fontSize: "1.3em",
        }}
      >
        {tabs.map((tab) => (
          <button
            key={tab.name}
            onClick={() => setActiveTab(tab.name)}
            style={{
              backgroundColor: activeTab === tab.name ? "#fff" : "#f0f0f1",
              color: activeTab === tab.name ? "black" : "black",
              border: "none",
              borderTop: activeTab === tab.name ? "solid 2px #5e2129" : "2px solid rgba(0,0,0,0)",
              borderLeft: activeTab === tab.name ? "solid 2px #5e2129" : "none",
              borderRight: activeTab === tab.name ? "solid 2px #5e2129" : "none",
              borderBottom: activeTab === tab.name ? "none" : "solid 2px #5e2129",
              padding: "10px 20px",
              cursor: "pointer",
            }}
          >
            {tab.title}
          </button>
        ))}
      </div>

      <div
        style={{
          id: "option-body-container",
          backgroundColor: "white",
          padding: "10px 0",
          border: "2px solid #5e2129",
          marginTop: "-2px",
          marginRight: "20px",
          /*filter: "drop-shadow(-10px 10px 20px rgba(0,0,0,0.1))",*/
        }}
      >
        {activeTab === "tab1" && (
          <div style={{ padding: "25px 75px" }}>
          <h2>General Settings</h2>
          <p>
            Selected items in the list will be included in the WordPres
            built-in search feature. The ones unchecked will be excluded.
          </p>
          <button type="button"
            onClick={handleUpdateClick}
            className="button button-primary"
          >
            Update
          </button>
          {updateSuccess && <span style={{ color: 'green', marginLeft: '10px' }}>Settings updated successfully.</span>}
        </div>
        )}
        {activeTab === "tab2" && (
          <div style={{ padding: "25px 75px" }}>
            <h2>Select or deselect a post type</h2>
            <p>
              Selected items in the list will be included in the WordPres
              built-in search feature. The ones unchecked will be excluded.
            </p>
            {postTypes.map((postType) => (
              <div key={postType} style={{ margin: "10px 0" }}>
                <label>
                  <input
                    type="checkbox"
                    checked={checkedPostTypes[postType]}
                    onChange={() => handleCheckChange(postType)}
                  />
                  {postType}
                </label>
              </div>
            ))}
            <button type="button"
              onClick={handleUpdateClick}
              className="button button-primary"
            >
              Update
            </button>
            {updateSuccess && <span style={{ color: 'green', marginLeft: '10px' }}>Settings updated successfully.</span>}
          </div>
        )}
        {activeTab === "tab3" && (
          <div>
            <h3>Tab 3 Content</h3>
          </div>
        )}
      </div>
    </div>
  );
}

render(
  <App />,
  document.getElementById("obi-exclude-from-search-options")
);
