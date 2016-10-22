package movies_component;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.nio.file.Files;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;

import com.mongodb.client.MongoDatabase;
import content_analyzer.StanfordLemmatizer;
import models.*;

/**
 * Class to handle the MySQL Database functionality
 * Created by Sofia on 4/3/2016.
 */
public class MovieStoragerSQL {
    private static Connection conn;

    /**
     * Constructor of the class; initializes the connection to the Database
     */
    public MovieStoragerSQL() {
        MySQLDatabase db = new MySQLDatabase();
        if (db.connect()) {
            conn = db.getConnection();
        }
    }

    /**
     * Closes the connection to the Database
     * @return True if the close was successful, false otherwise
     */
    public boolean closeConnection() {
        try {
            conn.close();
            return true;
        } catch (SQLException e) {
            return false;
        }
    }

    /**
     * Inserts a movie into the Database
     * @param m The movie to be inserted
     * @throws SQLException In case a connection is not open or there is a problem with the inserted values
     */
    void InsertMovietoDB(Movie m) throws SQLException {
        // The mysql insert statement
        String query = " insert into all_movies (title, year, categories, wikipedia_page, " +
                "imdb_url, extended_plot)" + " values (?, ?, ?, ?, ?, ?)";

        // Create the mysql insert PreparedStatement
        PreparedStatement preparedStmt = conn.prepareStatement(query);
        preparedStmt.setString(1, m.getTitle());
        preparedStmt.setInt(2, m.getYear());
        preparedStmt.setString(3, m.getCategories());
        preparedStmt.setString(4, m.getWikipediaPage());
        preparedStmt.setString(5, m.getImdbURL());
        preparedStmt.setString(6, m.getExtendedPlot());

            // Execute the PreparedStatement
        try {
            preparedStmt.execute();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    /**
     * Deletes a row from the Database
     * @param id The row's ID
     */
    public void deleteMovie(String id) {
        String query = " DELETE FROM `all_movies` WHERE id= ? ";
        PreparedStatement preparedStmtAux;
        try {
            preparedStmtAux = conn.prepareStatement(query);
            preparedStmtAux.setString(1, id);
            preparedStmtAux.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    /**
     * Inserts the film's semantics_plot column value
     * @param id The ID of the film
     * @param semantics The semantics plot
     */
    public void insertSemanticPlot(String id, String semantics) {
        insertField(id,semantics,"semantics_plot");
    }

    /**
     * Inserts the film's parsed_plot column value
     * @param id The ID of the film
     * @param parsed The parsed plot
     */
    public void insertParsedPlot(String id, String parsed) {
        insertField(id,parsed,"parsed_plot");
    }

    /**
     * Inserts the film's continents column value
     * @param id The ID of the film
     * @param continents The continents
     */
    public void insertContinents(String id, String continents) {
        insertField(id,continents,"continents");
    }

    /**
     * Checks if a field is null
     * @param id The id of the movie to be checked
     * @throws SQLException In case the connection with the MySQL database fails
     * @return True if the field is null, false otherwise
     */
    public boolean checkIfFieldIsNull(String id, String field) throws SQLException {
        String query;
        query = "SELECT * FROM `all_movies` WHERE `id`= " + id + " AND " + field + " IS NULL";
        PreparedStatement preparedStmtAux;
        // Execute the PreparedStatement

        ResultSet set = null;
        try {
            // Create the mysql select PreparedStatement
            PreparedStatement statement = conn.prepareStatement(query);
            // Execute the PreparedStatement
            set = statement.executeQuery(query);
        } catch (Exception e) {
            System.out.println("Exception in query method:\n" + e.getMessage());
        }
        return (set.isBeforeFirst()); //If set is not null, then the semantics_plot is null
    }

    /**
     * Checks if continents is null
     * @param id The id of the movie to be checked
     * @throws SQLException In case the connection with the MySQL database fails
     * @return True if the continents field is null, false otherwise
     */
    public boolean checkIfContinentsIsNull(String id) throws SQLException {
        return checkIfFieldIsNull(id, "continents");
    }

    /**
     * Checks if semantics_plot is null
     * @param id The id of the movie to be checked
     * @throws SQLException In case the connection with the MySQL database fails
     * @return True if the semantics_plot is null, false otherwise
     */
    public boolean checkIfPlotIsNull(String id) throws SQLException {
        return checkIfFieldIsNull(id, "semantics_plot");
    }

    /**
     * Inserts fields in the DB
     * @param id The ID of the film
     * @param field The content of the field to be added
     * @param field_type The type of the field (name of the column); e.g. "parsed_plot" for parsed_plot
     */
    public void insertField(String id, String field, String field_type) {
        String query = " UPDATE `all_movies` SET " + field_type  + " = ? WHERE `id`= ? ";

        PreparedStatement preparedStmtAux;
        try {
            preparedStmtAux = conn.prepareStatement(query);
            preparedStmtAux.setString(1, field);
            preparedStmtAux.setString(2, id);
            preparedStmtAux.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    /**
     * Inserts numeric fields in the DB
     * @param id The ID of the film
     * @param field The numeric field to be added
     * @param field_type The type of the field (name of the column)
     */
    public void insertField(String id, Double field, String field_type) {
        if(field != null) {
            String query = " UPDATE `all_movies` SET " + field_type + " = ? WHERE `id`= ? ";

            PreparedStatement preparedStmtAux;
            try {
                preparedStmtAux = conn.prepareStatement(query);
                preparedStmtAux.setDouble(1, field);
                preparedStmtAux.setString(2, id);
                preparedStmtAux.executeUpdate();
            } catch (SQLException e) {
                e.printStackTrace();
            }
        }
    }

    /**
     * Executes a result query and returns the results
     * @param query The query to be executed in a String format
     * @return The results in a ResultSet format
     */
    public ResultSet selectQuery(String query){
        ResultSet set = null;
        try {
            // Create the mysql select PreparedStatement
            PreparedStatement statement = conn.prepareStatement(query);
            // Execute the PreparedStatement
            set = statement.executeQuery(query);
        } catch (Exception e) {
            System.out.println("Exception in query method:\n" + e.getMessage());
        }
        return set;
    }
}
